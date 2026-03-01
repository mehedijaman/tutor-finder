<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display activity logs with filters.
     */
    public function index(Request $request): Response
    {
        $search = trim($request->string('search')->toString());
        $sort = $request->string('sort')->toString();

        if (! in_array($sort, ['id', 'log_name', 'description', 'event', 'created_at'], true)) {
            $sort = 'created_at';
        }

        $direction = strtolower($request->string('direction')->toString());

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = $sort === 'created_at' ? 'desc' : 'asc';
        }

        $items = Activity::query()
            ->with(['causer', 'subject'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('description', 'like', "%{$search}%")
                        ->orWhere('log_name', 'like', "%{$search}%")
                        ->orWhere('event', 'like', "%{$search}%")
                        ->orWhere('subject_type', 'like', "%{$search}%")
                        ->orWhere('causer_type', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Activity $activity) => [
                'id' => $activity->id,
                'log_name' => $activity->log_name,
                'description' => $activity->description,
                'event' => $activity->event,
                'causer' => $this->resolveModelLabel(
                    $activity->causer_type,
                    $activity->causer_id,
                    $activity->causer,
                    'System',
                ),
                'subject' => $this->resolveModelLabel(
                    $activity->subject_type,
                    $activity->subject_id,
                    $activity->subject,
                    '—',
                ),
                'properties' => $this->normalizeProperties($activity->properties),
                'created_at' => $activity->created_at?->toDateTimeString(),
            ]);

        return inertia('admin/activity-logs/Index', [
            'items' => $items,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    /**
     * Convert related model details to a readable log label.
     */
    private function resolveModelLabel(?string $type, mixed $id, mixed $model, string $fallback): string
    {
        if ($type === null || $id === null) {
            return $fallback;
        }

        $baseLabel = class_basename($type).' #'.$id;

        if (! $model instanceof Model) {
            return $baseLabel;
        }

        $name = $model->getAttribute('name');

        if (is_string($name) && $name !== '') {
            return "{$name} ({$baseLabel})";
        }

        $email = $model->getAttribute('email');

        if (is_string($email) && $email !== '') {
            return "{$email} ({$baseLabel})";
        }

        return $baseLabel;
    }

    /**
     * Normalize activity properties for JSON serialization.
     *
     * @return array<string, mixed>
     */
    private function normalizeProperties(mixed $properties): array
    {
        if ($properties instanceof Collection) {
            return $properties->toArray();
        }

        if (is_array($properties)) {
            return $properties;
        }

        return [];
    }
}

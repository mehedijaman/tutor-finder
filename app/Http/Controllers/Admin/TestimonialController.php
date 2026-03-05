<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TaxonomyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTestimonialRequest;
use App\Http\Requests\UpdateTestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class TestimonialController extends Controller
{
    /**
     * Display testimonial list or recycle bin.
     */
    public function index(Request $request): Response
    {
        $showTrash = $request->boolean('trash');
        $query = trim($request->string('q')->toString());

        $items = Testimonial::query()
            ->with('media')
            ->when($showTrash, fn (Builder $builder) => $builder->onlyTrashed())
            ->when($query !== '', function (Builder $builder) use ($query): void {
                $builder->where(function (Builder $searchQuery) use ($query): void {
                    $searchQuery
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('role', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Testimonial $testimonial): array => [
                'id' => $testimonial->id,
                'user_id' => $testimonial->user_id,
                'name' => $testimonial->name,
                'role' => $testimonial->role,
                'avatar_url' => $testimonial->getFirstMediaUrl('avatar') ?: $testimonial->avatar_url,
                'content' => $testimonial->content,
                'rating' => $testimonial->rating,
                'status' => $testimonial->status instanceof TaxonomyStatus
                    ? $testimonial->status->value
                    : (string) $testimonial->status,
                'sort_order' => $testimonial->sort_order,
                'updated_at' => $testimonial->updated_at?->toDateTimeString(),
                'deleted_at' => $testimonial->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/testimonials/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'q' => $query,
            ],
            'counts' => [
                'active' => Testimonial::query()->count(),
                'trash' => Testimonial::query()->onlyTrashed()->count(),
            ],
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Store testimonial.
     */
    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        $validated = $request->safe()->except(['avatar']);

        $testimonial = Testimonial::query()->create($validated);

        if ($request->hasFile('avatar')) {
            $testimonial->addMediaFromRequest('avatar')
                ->toMediaCollection('avatar');
        }

        return redirect()
            ->route('admin.testimonials.index')
            ->with('status', 'Testimonial created successfully.');
    }

    /**
     * Update testimonial.
     */
    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $validated = $request->safe()->except(['avatar', 'remove_avatar']);

        $testimonial->update($validated);

        if ($request->boolean('remove_avatar')) {
            $testimonial->clearMediaCollection('avatar');
        }

        if ($request->hasFile('avatar')) {
            $testimonial->addMediaFromRequest('avatar')
                ->toMediaCollection('avatar');
        }

        return redirect()
            ->route('admin.testimonials.index')
            ->with('status', 'Testimonial updated successfully.');
    }

    /**
     * Move testimonial to recycle bin.
     */
    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return redirect()
            ->back()
            ->with('status', 'Testimonial moved to recycle bin.');
    }

    /**
     * Restore testimonial from recycle bin.
     */
    public function restore(int $id): RedirectResponse
    {
        $testimonial = Testimonial::query()->withTrashed()->findOrFail($id);

        if (! $testimonial->trashed()) {
            return redirect()
                ->back()
                ->withErrors(['testimonial' => 'Only trashed testimonials can be restored.']);
        }

        $testimonial->restore();

        return redirect()
            ->back()
            ->with('status', 'Testimonial restored successfully.');
    }

    /**
     * Permanently delete testimonial.
     */
    public function forceDelete(int $id): RedirectResponse
    {
        $testimonial = Testimonial::query()->withTrashed()->findOrFail($id);

        if (! $testimonial->trashed()) {
            return redirect()
                ->back()
                ->withErrors(['testimonial' => 'Only trashed testimonials can be permanently deleted.']);
        }

        $testimonial->forceDelete();

        return redirect()
            ->back()
            ->with('status', 'Testimonial permanently deleted.');
    }

    /**
     * Empty testimonial recycle bin.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $count = Testimonial::query()->onlyTrashed()->count();

        Testimonial::query()->onlyTrashed()->forceDelete();

        return redirect()
            ->back()
            ->with('status', "Deleted {$count} testimonial(s) from recycle bin.");
    }

    /**
     * Get status options.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return [
            ['value' => TaxonomyStatus::Active->value, 'label' => 'Active'],
            ['value' => TaxonomyStatus::Inactive->value, 'label' => 'Inactive'],
        ];
    }
}

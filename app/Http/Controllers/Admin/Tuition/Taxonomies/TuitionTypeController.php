<?php

namespace App\Http\Controllers\Admin\Tuition\Taxonomies;

use App\Enums\TaxonomyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tuition\Taxonomies\TaxonomyStatusUpdateRequest;
use App\Http\Requests\Admin\Tuition\Taxonomies\TuitionTypeStoreRequest;
use App\Http\Requests\Admin\Tuition\Taxonomies\TuitionTypeUpdateRequest;
use App\Models\TuitionType;
use App\Support\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class TuitionTypeController extends Controller
{
    /**
     * Display tuition type list or recycle bin.
     */
    public function index(Request $request): Response
    {
        $showTrash = $request->boolean('trash');
        $query = trim($request->string('q')->toString());
        $status = strtolower(trim($request->string('status')->toString()));

        if (! in_array($status, [TaxonomyStatus::Active->value, TaxonomyStatus::Inactive->value], true)) {
            $status = '';
        }

        $items = TuitionType::query()
            ->when($showTrash, fn ($builder) => $builder->onlyTrashed())
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($subQuery) use ($query): void {
                    $subQuery
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('slug', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                });
            })
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
            ->ordered()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (TuitionType $tuitionType): array => [
                'id' => $tuitionType->id,
                'name' => $tuitionType->name,
                'slug' => $tuitionType->slug,
                'description' => $tuitionType->description,
                'status' => $tuitionType->status,
                'sort_order' => $tuitionType->sort_order,
                'updated_at' => $tuitionType->updated_at?->toDateTimeString(),
                'deleted_at' => $tuitionType->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/tuition/taxonomies/tuition-types/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'q' => $query,
                'status' => $status,
            ],
            'counts' => [
                'active' => TuitionType::query()->count(),
                'trash' => TuitionType::query()->onlyTrashed()->count(),
            ],
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Show tuition type create page.
     */
    public function create(): Response
    {
        return inertia('admin/tuition/taxonomies/tuition-types/Create', [
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Store tuition type.
     */
    public function store(TuitionTypeStoreRequest $request, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));

        TuitionType::query()->create([
            'name' => $name,
            'slug' => $slugService->unique(TuitionType::class, $slugBase),
            'description' => $validated['description'] ?? null,
            'status' => (string) $validated['status'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()
            ->route('admin.tuition.taxonomies.tuition-types.index')
            ->with('status', 'Tuition type created successfully.');
    }

    /**
     * Show tuition type edit page.
     */
    public function edit(TuitionType $tuitionType): Response
    {
        return inertia('admin/tuition/taxonomies/tuition-types/Edit', [
            'tuitionType' => [
                'id' => $tuitionType->id,
                'name' => $tuitionType->name,
                'slug' => $tuitionType->slug,
                'description' => $tuitionType->description,
                'status' => $tuitionType->status,
                'sort_order' => $tuitionType->sort_order,
            ],
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Update tuition type.
     */
    public function update(TuitionTypeUpdateRequest $request, TuitionType $tuitionType, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));

        $tuitionType->update([
            'name' => $name,
            'slug' => $slugService->unique(TuitionType::class, $slugBase, $tuitionType->id),
            'description' => $validated['description'] ?? null,
            'status' => (string) $validated['status'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()
            ->route('admin.tuition.taxonomies.tuition-types.index')
            ->with('status', 'Tuition type updated successfully.');
    }

    /**
     * Update tuition type status.
     */
    public function updateStatus(TaxonomyStatusUpdateRequest $request, TuitionType $tuitionType): RedirectResponse
    {
        $tuitionType->update([
            'status' => (string) $request->validated('status'),
        ]);

        return redirect()->back()->with('status', 'Tuition type status updated successfully.');
    }

    /**
     * Move tuition type to recycle bin.
     */
    public function destroy(TuitionType $tuitionType): RedirectResponse
    {
        $tuitionType->delete();

        return redirect()->back()->with('status', 'Tuition type moved to recycle bin.');
    }

    /**
     * Restore tuition type from recycle bin.
     */
    public function restore(TuitionType $tuitionType): RedirectResponse
    {
        if (! $tuitionType->trashed()) {
            return redirect()
                ->route('admin.tuition.taxonomies.tuition-types.index', ['trash' => 1])
                ->with('status', 'Tuition type is already active.');
        }

        $tuitionType->restore();

        return redirect()
            ->route('admin.tuition.taxonomies.tuition-types.index', ['trash' => 1])
            ->with('status', 'Tuition type restored successfully.');
    }

    /**
     * Permanently delete tuition type.
     */
    public function forceDelete(TuitionType $tuitionType): RedirectResponse
    {
        if (! $tuitionType->trashed()) {
            return redirect()->back()->withErrors(['tuitionType' => 'Only trashed tuition types can be permanently deleted.']);
        }

        $tuitionType->forceDelete();

        return redirect()->back()->with('status', 'Tuition type permanently deleted.');
    }

    /**
     * Empty tuition type recycle bin.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $count = TuitionType::query()->onlyTrashed()->count();

        TuitionType::query()->onlyTrashed()->forceDelete();

        return redirect()->back()->with('status', "Deleted {$count} tuition type(s) from recycle bin.");
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

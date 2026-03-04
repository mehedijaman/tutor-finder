<?php

namespace App\Http\Controllers\Admin\Tuition\Taxonomies;

use App\Enums\TaxonomyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tuition\Taxonomies\SchoolClassStoreRequest;
use App\Http\Requests\Admin\Tuition\Taxonomies\SchoolClassUpdateRequest;
use App\Http\Requests\Admin\Tuition\Taxonomies\TaxonomyStatusUpdateRequest;
use App\Models\Category;
use App\Models\SchoolClass;
use App\Support\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class SchoolClassController extends Controller
{
    /**
     * Display class list or recycle bin.
     */
    public function index(Request $request): Response
    {
        $showTrash = $request->boolean('trash');
        $query = trim($request->string('q')->toString());
        $status = strtolower(trim($request->string('status')->toString()));
        $categoryId = (int) $request->integer('category_id');

        if (! in_array($status, [TaxonomyStatus::Active->value, TaxonomyStatus::Inactive->value], true)) {
            $status = '';
        }

        if ($categoryId <= 0) {
            $categoryId = 0;
        }

        $items = SchoolClass::query()
            ->with('category:id,name')
            ->withCount('subjects')
            ->when($showTrash, fn ($builder) => $builder->onlyTrashed())
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($subQuery) use ($query): void {
                    $subQuery
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('slug', 'like', "%{$query}%");
                });
            })
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
            ->when($categoryId > 0, fn ($builder) => $builder->where('category_id', $categoryId))
            ->ordered()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (SchoolClass $schoolClass): array => [
                'id' => $schoolClass->id,
                'category_id' => $schoolClass->category_id,
                'category_name' => $schoolClass->category?->name,
                'name' => $schoolClass->name,
                'slug' => $schoolClass->slug,
                'status' => $schoolClass->status,
                'sort_order' => $schoolClass->sort_order,
                'subjects_count' => $schoolClass->subjects_count,
                'updated_at' => $schoolClass->updated_at?->toDateTimeString(),
                'deleted_at' => $schoolClass->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/tuition/taxonomies/classes/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'q' => $query,
                'status' => $status,
                'category_id' => $categoryId > 0 ? $categoryId : null,
            ],
            'counts' => [
                'active' => SchoolClass::query()->count(),
                'trash' => SchoolClass::query()->onlyTrashed()->count(),
            ],
            'categories' => $this->activeCategories(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Show class create page.
     */
    public function create(): Response
    {
        return inertia('admin/tuition/taxonomies/classes/Create', [
            'categories' => $this->activeCategories(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Store class.
     */
    public function store(SchoolClassStoreRequest $request, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));
        $categoryId = (int) $validated['category_id'];

        SchoolClass::query()->create([
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => $slugService->unique(SchoolClass::class, $slugBase, null, true, ['category_id' => $categoryId]),
            'status' => (string) $validated['status'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()
            ->route('admin.tuition.taxonomies.classes.index')
            ->with('status', 'Class created successfully.');
    }

    /**
     * Show class edit page.
     */
    public function edit(SchoolClass $schoolClass): Response
    {
        return inertia('admin/tuition/taxonomies/classes/Edit', [
            'schoolClass' => [
                'id' => $schoolClass->id,
                'category_id' => $schoolClass->category_id,
                'name' => $schoolClass->name,
                'slug' => $schoolClass->slug,
                'status' => $schoolClass->status,
                'sort_order' => $schoolClass->sort_order,
            ],
            'categories' => $this->activeCategories(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Update class.
     */
    public function update(SchoolClassUpdateRequest $request, SchoolClass $schoolClass, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));
        $categoryId = (int) $validated['category_id'];

        $schoolClass->update([
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => $slugService->unique(SchoolClass::class, $slugBase, $schoolClass->id, true, ['category_id' => $categoryId]),
            'status' => (string) $validated['status'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()
            ->route('admin.tuition.taxonomies.classes.index')
            ->with('status', 'Class updated successfully.');
    }

    /**
     * Update class status.
     */
    public function updateStatus(TaxonomyStatusUpdateRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->update([
            'status' => (string) $request->validated('status'),
        ]);

        return redirect()->back()->with('status', 'Class status updated successfully.');
    }

    /**
     * Move class to recycle bin.
     */
    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        if ($schoolClass->subjects()->exists()) {
            return redirect()
                ->back()
                ->withErrors(['schoolClass' => 'Class cannot be deleted while active subjects exist.']);
        }

        $schoolClass->delete();

        return redirect()->back()->with('status', 'Class moved to recycle bin.');
    }

    /**
     * Restore class from recycle bin.
     */
    public function restore(SchoolClass $schoolClass): RedirectResponse
    {
        if (! $schoolClass->trashed()) {
            return redirect()
                ->route('admin.tuition.taxonomies.classes.index', ['trash' => 1])
                ->with('status', 'Class is already active.');
        }

        if (! $schoolClass->category()->whereNull('deleted_at')->exists()) {
            return redirect()
                ->back()
                ->withErrors(['schoolClass' => 'Class cannot be restored because parent category is not active.']);
        }

        $schoolClass->restore();

        return redirect()
            ->route('admin.tuition.taxonomies.classes.index', ['trash' => 1])
            ->with('status', 'Class restored successfully.');
    }

    /**
     * Permanently delete class.
     */
    public function forceDelete(SchoolClass $schoolClass): RedirectResponse
    {
        if (! $schoolClass->trashed()) {
            return redirect()->back()->withErrors(['schoolClass' => 'Only trashed classes can be permanently deleted.']);
        }

        if ($schoolClass->subjects()->withTrashed()->exists()) {
            return redirect()
                ->back()
                ->withErrors(['schoolClass' => 'Class cannot be permanently deleted while child subjects exist.']);
        }

        $schoolClass->forceDelete();

        return redirect()->back()->with('status', 'Class permanently deleted.');
    }

    /**
     * Empty classes recycle bin.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $deleted = 0;
        $skipped = 0;

        SchoolClass::query()
            ->onlyTrashed()
            ->get()
            ->each(function (SchoolClass $schoolClass) use (&$deleted, &$skipped): void {
                if ($schoolClass->subjects()->withTrashed()->exists()) {
                    $skipped++;

                    return;
                }

                $schoolClass->forceDelete();
                $deleted++;
            });

        return redirect()
            ->back()
            ->with('status', "Deleted {$deleted} class(es). Skipped {$skipped} due to child dependencies.");
    }

    /**
     * Get active category options.
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function activeCategories(): array
    {
        return Category::query()
            ->where('status', TaxonomyStatus::Active)
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->all();
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

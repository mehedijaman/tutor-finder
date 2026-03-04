<?php

namespace App\Http\Controllers\Admin\Tuition\Taxonomies;

use App\Enums\TaxonomyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tuition\Taxonomies\CategoryStoreRequest;
use App\Http\Requests\Admin\Tuition\Taxonomies\CategoryUpdateRequest;
use App\Http\Requests\Admin\Tuition\Taxonomies\TaxonomyStatusUpdateRequest;
use App\Models\Category;
use App\Support\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display category list or recycle bin.
     */
    public function index(Request $request): Response
    {
        $showTrash = $request->boolean('trash');
        $query = trim($request->string('q')->toString());
        $status = strtolower(trim($request->string('status')->toString()));

        if (! in_array($status, [TaxonomyStatus::Active->value, TaxonomyStatus::Inactive->value], true)) {
            $status = '';
        }

        $items = Category::query()
            ->withCount('schoolClasses')
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
            ->through(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'status' => $category->status,
                'sort_order' => $category->sort_order,
                'classes_count' => $category->school_classes_count,
                'updated_at' => $category->updated_at?->toDateTimeString(),
                'deleted_at' => $category->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/tuition/taxonomies/categories/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'q' => $query,
                'status' => $status,
            ],
            'counts' => [
                'active' => Category::query()->count(),
                'trash' => Category::query()->onlyTrashed()->count(),
            ],
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Show category create page.
     */
    public function create(): Response
    {
        return inertia('admin/tuition/taxonomies/categories/Create', [
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Store category.
     */
    public function store(CategoryStoreRequest $request, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));

        Category::query()->create([
            'name' => $name,
            'slug' => $slugService->unique(Category::class, $slugBase),
            'description' => $validated['description'] ?? null,
            'status' => (string) $validated['status'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()
            ->route('admin.tuition.taxonomies.categories.index')
            ->with('status', 'Category created successfully.');
    }

    /**
     * Show category edit page.
     */
    public function edit(Category $category): Response
    {
        return inertia('admin/tuition/taxonomies/categories/Edit', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'status' => $category->status,
                'sort_order' => $category->sort_order,
            ],
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Update category.
     */
    public function update(CategoryUpdateRequest $request, Category $category, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));

        $category->update([
            'name' => $name,
            'slug' => $slugService->unique(Category::class, $slugBase, $category->id),
            'description' => $validated['description'] ?? null,
            'status' => (string) $validated['status'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()
            ->route('admin.tuition.taxonomies.categories.index')
            ->with('status', 'Category updated successfully.');
    }

    /**
     * Update category status.
     */
    public function updateStatus(TaxonomyStatusUpdateRequest $request, Category $category): RedirectResponse
    {
        $category->update([
            'status' => (string) $request->validated('status'),
        ]);

        return redirect()->back()->with('status', 'Category status updated successfully.');
    }

    /**
     * Move category to recycle bin.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->schoolClasses()->exists()) {
            return redirect()
                ->back()
                ->withErrors(['category' => 'Category cannot be deleted while active classes exist.']);
        }

        $category->delete();

        return redirect()->back()->with('status', 'Category moved to recycle bin.');
    }

    /**
     * Restore category from recycle bin.
     */
    public function restore(Category $category): RedirectResponse
    {
        if (! $category->trashed()) {
            return redirect()
                ->route('admin.tuition.taxonomies.categories.index', ['trash' => 1])
                ->with('status', 'Category is already active.');
        }

        $category->restore();

        return redirect()
            ->route('admin.tuition.taxonomies.categories.index', ['trash' => 1])
            ->with('status', 'Category restored successfully.');
    }

    /**
     * Permanently delete category.
     */
    public function forceDelete(Category $category): RedirectResponse
    {
        if (! $category->trashed()) {
            return redirect()->back()->withErrors(['category' => 'Only trashed categories can be permanently deleted.']);
        }

        if ($category->schoolClasses()->withTrashed()->exists()) {
            return redirect()
                ->back()
                ->withErrors(['category' => 'Category cannot be permanently deleted while child classes exist.']);
        }

        $category->forceDelete();

        return redirect()->back()->with('status', 'Category permanently deleted.');
    }

    /**
     * Empty categories recycle bin.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $deleted = 0;
        $skipped = 0;

        Category::query()
            ->onlyTrashed()
            ->get()
            ->each(function (Category $category) use (&$deleted, &$skipped): void {
                if ($category->schoolClasses()->withTrashed()->exists()) {
                    $skipped++;

                    return;
                }

                $category->forceDelete();
                $deleted++;
            });

        return redirect()
            ->back()
            ->with('status', "Deleted {$deleted} category(s). Skipped {$skipped} due to child dependencies.");
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

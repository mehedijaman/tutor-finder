<?php

namespace App\Http\Controllers\Admin\Tuition\Taxonomies;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tuition\Taxonomies\SubjectStoreRequest;
use App\Http\Requests\Admin\Tuition\Taxonomies\SubjectUpdateRequest;
use App\Http\Requests\Admin\Tuition\Taxonomies\TaxonomyStatusUpdateRequest;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Support\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class SubjectController extends Controller
{
    /**
     * Display subject list or recycle bin.
     */
    public function index(Request $request): Response
    {
        $showTrash = $request->boolean('trash');
        $query = trim($request->string('q')->toString());
        $status = strtolower(trim($request->string('status')->toString()));
        $classId = (int) $request->integer('class_id');

        if (! in_array($status, [Subject::STATUS_ACTIVE, Subject::STATUS_INACTIVE], true)) {
            $status = '';
        }

        if ($classId <= 0) {
            $classId = 0;
        }

        $items = Subject::query()
            ->with(['schoolClass:id,category_id,name', 'schoolClass.category:id,name'])
            ->when($showTrash, fn ($builder) => $builder->onlyTrashed())
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($subQuery) use ($query): void {
                    $subQuery
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('slug', 'like', "%{$query}%");
                });
            })
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
            ->when($classId > 0, fn ($builder) => $builder->where('class_id', $classId))
            ->ordered()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Subject $subject): array => [
                'id' => $subject->id,
                'class_id' => $subject->class_id,
                'class_name' => $subject->schoolClass?->name,
                'category_name' => $subject->schoolClass?->category?->name,
                'name' => $subject->name,
                'slug' => $subject->slug,
                'status' => $subject->status,
                'sort_order' => $subject->sort_order,
                'updated_at' => $subject->updated_at?->toDateTimeString(),
                'deleted_at' => $subject->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/tuition/taxonomies/subjects/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'q' => $query,
                'status' => $status,
                'class_id' => $classId > 0 ? $classId : null,
            ],
            'counts' => [
                'active' => Subject::query()->count(),
                'trash' => Subject::query()->onlyTrashed()->count(),
            ],
            'schoolClasses' => $this->activeSchoolClasses(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Show subject create page.
     */
    public function create(): Response
    {
        return inertia('admin/tuition/taxonomies/subjects/Create', [
            'schoolClasses' => $this->activeSchoolClasses(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Store subject.
     */
    public function store(SubjectStoreRequest $request, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));
        $classId = (int) $validated['class_id'];

        Subject::query()->create([
            'class_id' => $classId,
            'name' => $name,
            'slug' => $slugService->unique(Subject::class, $slugBase, null, true, ['class_id' => $classId]),
            'status' => (string) $validated['status'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()
            ->route('admin.tuition.taxonomies.subjects.index')
            ->with('status', 'Subject created successfully.');
    }

    /**
     * Show subject edit page.
     */
    public function edit(Subject $subject): Response
    {
        return inertia('admin/tuition/taxonomies/subjects/Edit', [
            'subject' => [
                'id' => $subject->id,
                'class_id' => $subject->class_id,
                'name' => $subject->name,
                'slug' => $subject->slug,
                'status' => $subject->status,
                'sort_order' => $subject->sort_order,
            ],
            'schoolClasses' => $this->activeSchoolClasses(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Update subject.
     */
    public function update(SubjectUpdateRequest $request, Subject $subject, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));
        $classId = (int) $validated['class_id'];

        $subject->update([
            'class_id' => $classId,
            'name' => $name,
            'slug' => $slugService->unique(Subject::class, $slugBase, $subject->id, true, ['class_id' => $classId]),
            'status' => (string) $validated['status'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()
            ->route('admin.tuition.taxonomies.subjects.index')
            ->with('status', 'Subject updated successfully.');
    }

    /**
     * Update subject status.
     */
    public function updateStatus(TaxonomyStatusUpdateRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update([
            'status' => (string) $request->validated('status'),
        ]);

        return redirect()->back()->with('status', 'Subject status updated successfully.');
    }

    /**
     * Move subject to recycle bin.
     */
    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()->back()->with('status', 'Subject moved to recycle bin.');
    }

    /**
     * Restore subject from recycle bin.
     */
    public function restore(Subject $subject): RedirectResponse
    {
        if (! $subject->trashed()) {
            return redirect()
                ->route('admin.tuition.taxonomies.subjects.index', ['trash' => 1])
                ->with('status', 'Subject is already active.');
        }

        if (! $subject->schoolClass()->whereNull('deleted_at')->exists()) {
            return redirect()
                ->back()
                ->withErrors(['subject' => 'Subject cannot be restored because parent class is not active.']);
        }

        $subject->restore();

        return redirect()
            ->route('admin.tuition.taxonomies.subjects.index', ['trash' => 1])
            ->with('status', 'Subject restored successfully.');
    }

    /**
     * Permanently delete subject.
     */
    public function forceDelete(Subject $subject): RedirectResponse
    {
        if (! $subject->trashed()) {
            return redirect()->back()->withErrors(['subject' => 'Only trashed subjects can be permanently deleted.']);
        }

        $subject->forceDelete();

        return redirect()->back()->with('status', 'Subject permanently deleted.');
    }

    /**
     * Empty subjects recycle bin.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $count = Subject::query()->onlyTrashed()->count();

        Subject::query()->onlyTrashed()->forceDelete();

        return redirect()->back()->with('status', "Deleted {$count} subject(s) from recycle bin.");
    }

    /**
     * Get active classes for form/filter options.
     *
     * @return array<int, array{id: int, name: string, category_name: string|null}>
     */
    private function activeSchoolClasses(): array
    {
        return SchoolClass::query()
            ->with('category:id,name')
            ->where('status', SchoolClass::STATUS_ACTIVE)
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'category_id', 'name'])
            ->map(fn (SchoolClass $schoolClass): array => [
                'id' => $schoolClass->id,
                'name' => $schoolClass->name,
                'category_name' => $schoolClass->category?->name,
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
            ['value' => Subject::STATUS_ACTIVE, 'label' => 'Active'],
            ['value' => Subject::STATUS_INACTIVE, 'label' => 'Inactive'],
        ];
    }
}

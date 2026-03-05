<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TutorialAudience;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TutorialStoreRequest;
use App\Http\Requests\Admin\TutorialUpdateRequest;
use App\Models\Tutorial;
use App\Support\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class TutorialController extends Controller
{
    public function __construct(
        private SlugService $slugService,
    ) {}

    public function index(Request $request): Response
    {
        $isTrash = $request->boolean('trash');
        $query = trim($request->string('q')->toString());
        $audience = strtolower(trim($request->string('audience')->toString()));

        $audienceValues = array_column(TutorialAudience::cases(), 'value');
        if (! in_array($audience, $audienceValues, true)) {
            $audience = '';
        }

        $items = Tutorial::query()
            ->when($isTrash, fn ($builder) => $builder->onlyTrashed())
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($subQuery) use ($query): void {
                    $subQuery
                        ->where('title', 'like', "%{$query}%")
                        ->orWhere('slug', 'like', "%{$query}%");
                });
            })
            ->when($audience !== '', fn ($builder) => $builder->where('audience', $audience))
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Tutorial $tutorial): array => [
                'id' => $tutorial->id,
                'title' => $tutorial->title,
                'slug' => $tutorial->slug,
                'video_url' => $tutorial->video_url,
                'audience' => $tutorial->audience,
                'is_active' => $tutorial->is_active,
                'sort_order' => $tutorial->sort_order,
                'thumbnail_url' => $tutorial->getFirstMediaUrl('thumbnail') ?: null,
                'created_at' => $tutorial->created_at?->toDateTimeString(),
            ]);

        return inertia('admin/tutorials/Index', [
            'items' => $items,
            'filters' => [
                'q' => $query,
                'audience' => $audience,
                'trash' => $isTrash,
            ],
            'counts' => [
                'active' => Tutorial::query()->count(),
                'trash' => Tutorial::query()->onlyTrashed()->count(),
            ],
            'audienceOptions' => $this->audienceOptions(),
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/tutorials/Create', [
            'audienceOptions' => $this->audienceOptions(),
        ]);
    }

    public function store(TutorialStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $slug = $this->slugService->unique(Tutorial::class, $validated['slug'] ?: $validated['title']);

        $tutorial = Tutorial::query()->create([
            'title' => $validated['title'],
            'slug' => $slug,
            'video_url' => $validated['video_url'],
            'audience' => $validated['audience'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        if ($request->hasFile('thumbnail')) {
            $tutorial->addMediaFromRequest('thumbnail')
                ->toMediaCollection('thumbnail');
        }

        return redirect()
            ->route('admin.tutorials.index')
            ->with('status', 'Tutorial created successfully.');
    }

    public function edit(Tutorial $tutorial): Response
    {
        return inertia('admin/tutorials/Edit', [
            'tutorial' => [
                'id' => $tutorial->id,
                'title' => $tutorial->title,
                'slug' => $tutorial->slug,
                'video_url' => $tutorial->video_url,
                'audience' => $tutorial->audience,
                'description' => $tutorial->description,
                'is_active' => $tutorial->is_active,
                'sort_order' => $tutorial->sort_order,
                'thumbnail_url' => $tutorial->getFirstMediaUrl('thumbnail') ?: null,
            ],
            'audienceOptions' => $this->audienceOptions(),
        ]);
    }

    public function update(TutorialUpdateRequest $request, Tutorial $tutorial): RedirectResponse
    {
        $validated = $request->validated();
        $slug = $this->slugService->unique(Tutorial::class, $validated['slug'] ?: $validated['title'], $tutorial->id);

        $tutorial->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'video_url' => $validated['video_url'],
            'audience' => $validated['audience'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? $tutorial->is_active,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        if ($request->boolean('remove_thumbnail')) {
            $tutorial->clearMediaCollection('thumbnail');
        }

        if ($request->hasFile('thumbnail')) {
            $tutorial->addMediaFromRequest('thumbnail')
                ->toMediaCollection('thumbnail');
        }

        return redirect()
            ->route('admin.tutorials.index')
            ->with('status', 'Tutorial updated successfully.');
    }

    public function destroy(Tutorial $tutorial): RedirectResponse
    {
        $tutorial->delete();

        return redirect()->back()->with('status', 'Tutorial moved to recycle bin.');
    }

    public function restore(Tutorial $tutorial): RedirectResponse
    {
        $tutorial->restore();

        return redirect()->back()->with('status', 'Tutorial restored successfully.');
    }

    public function forceDelete(Tutorial $tutorial): RedirectResponse
    {
        $tutorial->clearMediaCollection('thumbnail');
        $tutorial->forceDelete();

        return redirect()->back()->with('status', 'Tutorial permanently deleted.');
    }

    public function emptyRecycleBin(): RedirectResponse
    {
        $trashedTutorials = Tutorial::query()->onlyTrashed()->get();

        foreach ($trashedTutorials as $tutorial) {
            $tutorial->clearMediaCollection('thumbnail');
            $tutorial->forceDelete();
        }

        return redirect()->back()->with('status', 'Recycle bin emptied successfully.');
    }

    public function updateStatus(Request $request, Tutorial $tutorial): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $tutorial->update(['is_active' => $validated['is_active']]);

        return redirect()->back()->with('status', 'Tutorial status updated successfully.');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function audienceOptions(): array
    {
        return array_map(
            fn (TutorialAudience $audience): array => [
                'value' => $audience->value,
                'label' => $audience->label(),
            ],
            TutorialAudience::cases(),
        );
    }
}

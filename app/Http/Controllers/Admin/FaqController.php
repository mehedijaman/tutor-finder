<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqStatusUpdateRequest;
use App\Http\Requests\Admin\FaqStoreRequest;
use App\Http\Requests\Admin\FaqUpdateRequest;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class FaqController extends Controller
{
    /**
     * Display FAQ list or recycle bin.
     */
    public function index(Request $request): Response
    {
        $showTrash = $request->boolean('trash');
        $query = trim($request->string('q')->toString());
        $audience = strtolower(trim($request->string('audience')->toString()));
        $status = strtolower(trim($request->string('status')->toString()));

        if (! in_array($audience, [Faq::AUDIENCE_TUTOR, Faq::AUDIENCE_GUARDIAN, Faq::AUDIENCE_BOTH], true)) {
            $audience = '';
        }

        if (! in_array($status, [Faq::STATUS_ACTIVE, Faq::STATUS_INACTIVE], true)) {
            $status = '';
        }

        $items = Faq::query()
            ->when($showTrash, fn ($builder) => $builder->onlyTrashed())
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($subQuery) use ($query): void {
                    $subQuery
                        ->where('question', 'like', "%{$query}%")
                        ->orWhere('answer', 'like', "%{$query}%");
                });
            })
            ->when($audience !== '', fn ($builder) => $builder->where('audience', $audience))
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Faq $faq): array => [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'audience' => $faq->audience,
                'status' => $faq->status,
                'sort_order' => $faq->sort_order,
                'updated_at' => $faq->updated_at?->toDateTimeString(),
                'created_at' => $faq->created_at?->toDateTimeString(),
                'deleted_at' => $faq->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/faqs/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'q' => $query,
                'audience' => $audience,
                'status' => $status,
            ],
            'counts' => [
                'active' => Faq::query()->count(),
                'trash' => Faq::query()->onlyTrashed()->count(),
            ],
            'audienceOptions' => $this->audienceOptions(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Show the create FAQ page.
     */
    public function create(): Response
    {
        return inertia('admin/faqs/Create', [
            'audienceOptions' => $this->audienceOptions(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Persist a new FAQ.
     */
    public function store(FaqStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Faq::query()->create([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'audience' => $validated['audience'],
            'status' => $validated['status'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('admin.faqs.index')
            ->with('status', 'FAQ created successfully.');
    }

    /**
     * Show the edit FAQ page.
     */
    public function edit(Faq $faq): Response
    {
        return inertia('admin/faqs/Edit', [
            'faq' => [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'audience' => $faq->audience,
                'status' => $faq->status,
                'sort_order' => $faq->sort_order,
            ],
            'audienceOptions' => $this->audienceOptions(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Update an existing FAQ.
     */
    public function update(FaqUpdateRequest $request, Faq $faq): RedirectResponse
    {
        $validated = $request->validated();

        $faq->update([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'audience' => $validated['audience'],
            'status' => $validated['status'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('admin.faqs.index')
            ->with('status', 'FAQ updated successfully.');
    }

    /**
     * Move FAQ to recycle bin.
     */
    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()
            ->back()
            ->with('status', 'FAQ moved to recycle bin.');
    }

    /**
     * Update FAQ status from the list view.
     */
    public function updateStatus(FaqStatusUpdateRequest $request, Faq $faq): RedirectResponse
    {
        $validated = $request->validated();

        $faq->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->back()
            ->with('status', 'FAQ status updated successfully.');
    }

    /**
     * Restore a trashed FAQ.
     */
    public function restore(Faq $faq): RedirectResponse
    {
        if (! $faq->trashed()) {
            return redirect()
                ->route('admin.faqs.index', ['trash' => 1])
                ->with('status', 'FAQ is already active.');
        }

        $faq->restore();

        return redirect()
            ->route('admin.faqs.index', ['trash' => 1])
            ->with('status', 'FAQ restored successfully.');
    }

    /**
     * Permanently delete a trashed FAQ.
     */
    public function forceDelete(Faq $faq): RedirectResponse
    {
        if (! $faq->trashed()) {
            return redirect()
                ->back()
                ->withErrors(['faq' => 'Only trashed FAQs can be permanently deleted.']);
        }

        $faq->forceDelete();

        return redirect()
            ->back()
            ->with('status', 'FAQ permanently deleted.');
    }

    /**
     * Empty FAQ recycle bin.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $count = Faq::query()->onlyTrashed()->count();

        Faq::query()->onlyTrashed()->forceDelete();

        return redirect()
            ->back()
            ->with('status', "Deleted {$count} FAQ(s) from recycle bin.");
    }

    /**
     * Get available audience options for forms and filters.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function audienceOptions(): array
    {
        return [
            ['value' => Faq::AUDIENCE_BOTH, 'label' => 'Both'],
            ['value' => Faq::AUDIENCE_TUTOR, 'label' => 'Tutor'],
            ['value' => Faq::AUDIENCE_GUARDIAN, 'label' => 'Guardian'],
        ];
    }

    /**
     * Get available status options for forms and filters.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return [
            ['value' => Faq::STATUS_ACTIVE, 'label' => 'Active'],
            ['value' => Faq::STATUS_INACTIVE, 'label' => 'Inactive'],
        ];
    }
}

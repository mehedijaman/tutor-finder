<?php

namespace App\Http\Controllers\Admin\Tuition\Taxonomies;

use App\Enums\TaxonomyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tuition\Taxonomies\CountryStoreRequest;
use App\Http\Requests\Admin\Tuition\Taxonomies\CountryUpdateRequest;
use App\Http\Requests\Admin\Tuition\Taxonomies\TaxonomyStatusUpdateRequest;
use App\Models\Country;
use App\Support\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class CountryController extends Controller
{
    /**
     * Display country list or recycle bin.
     */
    public function index(Request $request): Response
    {
        $showTrash = $request->boolean('trash');
        $query = trim($request->string('q')->toString());
        $status = strtolower(trim($request->string('status')->toString()));

        if (! in_array($status, [TaxonomyStatus::Active->value, TaxonomyStatus::Inactive->value], true)) {
            $status = '';
        }

        $items = Country::query()
            ->when($showTrash, fn ($builder) => $builder->onlyTrashed())
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($subQuery) use ($query): void {
                    $subQuery
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('slug', 'like', "%{$query}%");
                });
            })
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
            ->ordered()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Country $country): array => [
                'id' => $country->id,
                'name' => $country->name,
                'slug' => $country->slug,
                'status' => $country->status,
                'cities_count' => $country->cities()->count(),
                'updated_at' => $country->updated_at?->toDateTimeString(),
                'deleted_at' => $country->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/tuition/taxonomies/countries/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'q' => $query,
                'status' => $status,
            ],
            'counts' => [
                'active' => Country::query()->count(),
                'trash' => Country::query()->onlyTrashed()->count(),
            ],
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Show country create page.
     */
    public function create(): Response
    {
        return inertia('admin/tuition/taxonomies/countries/Create', [
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Store a new country.
     */
    public function store(CountryStoreRequest $request, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));

        Country::query()->create([
            'name' => $name,
            'slug' => $slugService->unique(Country::class, $slugBase),
            'status' => (string) $validated['status'],
        ]);

        return redirect()
            ->route('admin.tuition.taxonomies.countries.index')
            ->with('status', 'Country created successfully.');
    }

    /**
     * Show country edit page.
     */
    public function edit(Country $country): Response
    {
        return inertia('admin/tuition/taxonomies/countries/Edit', [
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
                'slug' => $country->slug,
                'status' => $country->status,
            ],
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Update country.
     */
    public function update(CountryUpdateRequest $request, Country $country, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));

        $country->update([
            'name' => $name,
            'slug' => $slugService->unique(Country::class, $slugBase, $country->id),
            'status' => (string) $validated['status'],
        ]);

        return redirect()
            ->route('admin.tuition.taxonomies.countries.index')
            ->with('status', 'Country updated successfully.');
    }

    /**
     * Update country status.
     */
    public function updateStatus(TaxonomyStatusUpdateRequest $request, Country $country): RedirectResponse
    {
        $country->update([
            'status' => (string) $request->validated('status'),
        ]);

        return redirect()->back()->with('status', 'Country status updated successfully.');
    }

    /**
     * Move country to recycle bin.
     */
    public function destroy(Country $country): RedirectResponse
    {
        if ($country->cities()->exists()) {
            return redirect()
                ->back()
                ->withErrors(['country' => 'Country cannot be deleted while active cities exist.']);
        }

        $country->delete();

        return redirect()->back()->with('status', 'Country moved to recycle bin.');
    }

    /**
     * Restore country from recycle bin.
     */
    public function restore(Country $country): RedirectResponse
    {
        if (! $country->trashed()) {
            return redirect()
                ->route('admin.tuition.taxonomies.countries.index', ['trash' => 1])
                ->with('status', 'Country is already active.');
        }

        $country->restore();

        return redirect()
            ->route('admin.tuition.taxonomies.countries.index', ['trash' => 1])
            ->with('status', 'Country restored successfully.');
    }

    /**
     * Permanently delete a country.
     */
    public function forceDelete(Country $country): RedirectResponse
    {
        if (! $country->trashed()) {
            return redirect()->back()->withErrors(['country' => 'Only trashed countries can be permanently deleted.']);
        }

        if ($country->cities()->withTrashed()->exists()) {
            return redirect()
                ->back()
                ->withErrors(['country' => 'Country cannot be permanently deleted while child cities exist.']);
        }

        $country->forceDelete();

        return redirect()->back()->with('status', 'Country permanently deleted.');
    }

    /**
     * Empty countries recycle bin.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $deleted = 0;
        $skipped = 0;

        Country::query()
            ->onlyTrashed()
            ->get()
            ->each(function (Country $country) use (&$deleted, &$skipped): void {
                if ($country->cities()->withTrashed()->exists()) {
                    $skipped++;

                    return;
                }

                $country->forceDelete();
                $deleted++;
            });

        return redirect()
            ->back()
            ->with('status', "Deleted {$deleted} country(s). Skipped {$skipped} due to child dependencies.");
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

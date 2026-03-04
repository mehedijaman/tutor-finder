<?php

namespace App\Http\Controllers\Admin\Tuition\Taxonomies;

use App\Enums\TaxonomyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tuition\Taxonomies\CityStoreRequest;
use App\Http\Requests\Admin\Tuition\Taxonomies\CityUpdateRequest;
use App\Http\Requests\Admin\Tuition\Taxonomies\TaxonomyStatusUpdateRequest;
use App\Models\City;
use App\Models\Country;
use App\Support\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class CityController extends Controller
{
    /**
     * Display city list or recycle bin.
     */
    public function index(Request $request): Response
    {
        $showTrash = $request->boolean('trash');
        $query = trim($request->string('q')->toString());
        $status = strtolower(trim($request->string('status')->toString()));
        $countryId = (int) $request->integer('country_id');

        if (! in_array($status, [TaxonomyStatus::Active->value, TaxonomyStatus::Inactive->value], true)) {
            $status = '';
        }

        if ($countryId <= 0) {
            $countryId = 0;
        }

        $items = City::query()
            ->with('country:id,name')
            ->when($showTrash, fn ($builder) => $builder->onlyTrashed())
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($subQuery) use ($query): void {
                    $subQuery
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('slug', 'like', "%{$query}%");
                });
            })
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
            ->when($countryId > 0, fn ($builder) => $builder->where('country_id', $countryId))
            ->ordered()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (City $city): array => [
                'id' => $city->id,
                'country_id' => $city->country_id,
                'country_name' => $city->country?->name,
                'name' => $city->name,
                'slug' => $city->slug,
                'status' => $city->status,
                'areas_count' => $city->areas()->count(),
                'updated_at' => $city->updated_at?->toDateTimeString(),
                'deleted_at' => $city->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/tuition/taxonomies/cities/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'q' => $query,
                'status' => $status,
                'country_id' => $countryId > 0 ? $countryId : null,
            ],
            'counts' => [
                'active' => City::query()->count(),
                'trash' => City::query()->onlyTrashed()->count(),
            ],
            'countries' => $this->activeCountries(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Show city create page.
     */
    public function create(): Response
    {
        return inertia('admin/tuition/taxonomies/cities/Create', [
            'countries' => $this->activeCountries(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Store a new city.
     */
    public function store(CityStoreRequest $request, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));
        $countryId = (int) $validated['country_id'];

        City::query()->create([
            'country_id' => $countryId,
            'name' => $name,
            'slug' => $slugService->unique(City::class, $slugBase, null, true, ['country_id' => $countryId]),
            'status' => (string) $validated['status'],
        ]);

        return redirect()
            ->route('admin.tuition.taxonomies.cities.index')
            ->with('status', 'City created successfully.');
    }

    /**
     * Show city edit page.
     */
    public function edit(City $city): Response
    {
        return inertia('admin/tuition/taxonomies/cities/Edit', [
            'city' => [
                'id' => $city->id,
                'country_id' => $city->country_id,
                'name' => $city->name,
                'slug' => $city->slug,
                'status' => $city->status,
            ],
            'countries' => $this->activeCountries(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Update city.
     */
    public function update(CityUpdateRequest $request, City $city, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));
        $countryId = (int) $validated['country_id'];

        $city->update([
            'country_id' => $countryId,
            'name' => $name,
            'slug' => $slugService->unique(City::class, $slugBase, $city->id, true, ['country_id' => $countryId]),
            'status' => (string) $validated['status'],
        ]);

        return redirect()
            ->route('admin.tuition.taxonomies.cities.index')
            ->with('status', 'City updated successfully.');
    }

    /**
     * Update city status.
     */
    public function updateStatus(TaxonomyStatusUpdateRequest $request, City $city): RedirectResponse
    {
        $city->update([
            'status' => (string) $request->validated('status'),
        ]);

        return redirect()->back()->with('status', 'City status updated successfully.');
    }

    /**
     * Move city to recycle bin.
     */
    public function destroy(City $city): RedirectResponse
    {
        if ($city->areas()->exists()) {
            return redirect()
                ->back()
                ->withErrors(['city' => 'City cannot be deleted while active areas exist.']);
        }

        $city->delete();

        return redirect()->back()->with('status', 'City moved to recycle bin.');
    }

    /**
     * Restore city from recycle bin.
     */
    public function restore(City $city): RedirectResponse
    {
        if (! $city->trashed()) {
            return redirect()
                ->route('admin.tuition.taxonomies.cities.index', ['trash' => 1])
                ->with('status', 'City is already active.');
        }

        if (! $city->country()->whereNull('deleted_at')->exists()) {
            return redirect()
                ->back()
                ->withErrors(['city' => 'City cannot be restored because parent country is not active.']);
        }

        $city->restore();

        return redirect()
            ->route('admin.tuition.taxonomies.cities.index', ['trash' => 1])
            ->with('status', 'City restored successfully.');
    }

    /**
     * Permanently delete a city.
     */
    public function forceDelete(City $city): RedirectResponse
    {
        if (! $city->trashed()) {
            return redirect()->back()->withErrors(['city' => 'Only trashed cities can be permanently deleted.']);
        }

        if ($city->areas()->withTrashed()->exists()) {
            return redirect()
                ->back()
                ->withErrors(['city' => 'City cannot be permanently deleted while child areas exist.']);
        }

        $city->forceDelete();

        return redirect()->back()->with('status', 'City permanently deleted.');
    }

    /**
     * Empty cities recycle bin.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $deleted = 0;
        $skipped = 0;

        City::query()
            ->onlyTrashed()
            ->get()
            ->each(function (City $city) use (&$deleted, &$skipped): void {
                if ($city->areas()->withTrashed()->exists()) {
                    $skipped++;

                    return;
                }

                $city->forceDelete();
                $deleted++;
            });

        return redirect()
            ->back()
            ->with('status', "Deleted {$deleted} city(s). Skipped {$skipped} due to child dependencies.");
    }

    /**
     * Get active countries for form/filter options.
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function activeCountries(): array
    {
        return Country::query()
            ->where('status', TaxonomyStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Country $country): array => [
                'id' => $country->id,
                'name' => $country->name,
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

<?php

namespace App\Http\Controllers\Admin\Tuition\Taxonomies;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tuition\Taxonomies\AreaStoreRequest;
use App\Http\Requests\Admin\Tuition\Taxonomies\AreaUpdateRequest;
use App\Http\Requests\Admin\Tuition\Taxonomies\TaxonomyStatusUpdateRequest;
use App\Models\Area;
use App\Models\City;
use App\Support\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class AreaController extends Controller
{
    /**
     * Display area list or recycle bin.
     */
    public function index(Request $request): Response
    {
        $showTrash = $request->boolean('trash');
        $query = trim($request->string('q')->toString());
        $status = strtolower(trim($request->string('status')->toString()));
        $cityId = (int) $request->integer('city_id');

        if (! in_array($status, [Area::STATUS_ACTIVE, Area::STATUS_INACTIVE], true)) {
            $status = '';
        }

        if ($cityId <= 0) {
            $cityId = 0;
        }

        $items = Area::query()
            ->with(['city:id,country_id,name', 'city.country:id,name'])
            ->when($showTrash, fn ($builder) => $builder->onlyTrashed())
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($subQuery) use ($query): void {
                    $subQuery
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('slug', 'like', "%{$query}%");
                });
            })
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
            ->when($cityId > 0, fn ($builder) => $builder->where('city_id', $cityId))
            ->ordered()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Area $area): array => [
                'id' => $area->id,
                'city_id' => $area->city_id,
                'city_name' => $area->city?->name,
                'country_name' => $area->city?->country?->name,
                'name' => $area->name,
                'slug' => $area->slug,
                'status' => $area->status,
                'updated_at' => $area->updated_at?->toDateTimeString(),
                'deleted_at' => $area->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/tuition/taxonomies/areas/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'q' => $query,
                'status' => $status,
                'city_id' => $cityId > 0 ? $cityId : null,
            ],
            'counts' => [
                'active' => Area::query()->count(),
                'trash' => Area::query()->onlyTrashed()->count(),
            ],
            'cities' => $this->activeCities(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Show area create page.
     */
    public function create(): Response
    {
        return inertia('admin/tuition/taxonomies/areas/Create', [
            'cities' => $this->activeCities(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Store a new area.
     */
    public function store(AreaStoreRequest $request, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));
        $cityId = (int) $validated['city_id'];

        Area::query()->create([
            'city_id' => $cityId,
            'name' => $name,
            'slug' => $slugService->unique(Area::class, $slugBase, null, true, ['city_id' => $cityId]),
            'status' => (string) $validated['status'],
        ]);

        return redirect()
            ->route('admin.tuition.taxonomies.areas.index')
            ->with('status', 'Area created successfully.');
    }

    /**
     * Show area edit page.
     */
    public function edit(Area $area): Response
    {
        return inertia('admin/tuition/taxonomies/areas/Edit', [
            'area' => [
                'id' => $area->id,
                'city_id' => $area->city_id,
                'name' => $area->name,
                'slug' => $area->slug,
                'status' => $area->status,
            ],
            'cities' => $this->activeCities(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Update area.
     */
    public function update(AreaUpdateRequest $request, Area $area, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));
        $cityId = (int) $validated['city_id'];

        $area->update([
            'city_id' => $cityId,
            'name' => $name,
            'slug' => $slugService->unique(Area::class, $slugBase, $area->id, true, ['city_id' => $cityId]),
            'status' => (string) $validated['status'],
        ]);

        return redirect()
            ->route('admin.tuition.taxonomies.areas.index')
            ->with('status', 'Area updated successfully.');
    }

    /**
     * Update area status.
     */
    public function updateStatus(TaxonomyStatusUpdateRequest $request, Area $area): RedirectResponse
    {
        $area->update([
            'status' => (string) $request->validated('status'),
        ]);

        return redirect()->back()->with('status', 'Area status updated successfully.');
    }

    /**
     * Move area to recycle bin.
     */
    public function destroy(Area $area): RedirectResponse
    {
        $area->delete();

        return redirect()->back()->with('status', 'Area moved to recycle bin.');
    }

    /**
     * Restore area from recycle bin.
     */
    public function restore(Area $area): RedirectResponse
    {
        if (! $area->trashed()) {
            return redirect()
                ->route('admin.tuition.taxonomies.areas.index', ['trash' => 1])
                ->with('status', 'Area is already active.');
        }

        if (! $area->city()->whereNull('deleted_at')->exists()) {
            return redirect()
                ->back()
                ->withErrors(['area' => 'Area cannot be restored because parent city is not active.']);
        }

        $area->restore();

        return redirect()
            ->route('admin.tuition.taxonomies.areas.index', ['trash' => 1])
            ->with('status', 'Area restored successfully.');
    }

    /**
     * Permanently delete area.
     */
    public function forceDelete(Area $area): RedirectResponse
    {
        if (! $area->trashed()) {
            return redirect()->back()->withErrors(['area' => 'Only trashed areas can be permanently deleted.']);
        }

        $area->forceDelete();

        return redirect()->back()->with('status', 'Area permanently deleted.');
    }

    /**
     * Empty areas recycle bin.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $count = Area::query()->onlyTrashed()->count();

        Area::query()->onlyTrashed()->forceDelete();

        return redirect()->back()->with('status', "Deleted {$count} area(s) from recycle bin.");
    }

    /**
     * Get active city options.
     *
     * @return array<int, array{id: int, name: string, country_name: string|null}>
     */
    private function activeCities(): array
    {
        return City::query()
            ->with('country:id,name')
            ->where('status', City::STATUS_ACTIVE)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'country_id', 'name'])
            ->map(fn (City $city): array => [
                'id' => $city->id,
                'name' => $city->name,
                'country_name' => $city->country?->name,
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
            ['value' => Area::STATUS_ACTIVE, 'label' => 'Active'],
            ['value' => Area::STATUS_INACTIVE, 'label' => 'Inactive'],
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Enums\TaxonomyStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\SlugService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuickTaxonomyController extends Controller
{
    public function __construct(
        protected SlugService $slugService
    ) {}

    /**
     * Store a quick taxonomy item (Subject, Class, Category).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(['subject', 'class', 'category'])],
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer'],
        ]);

        $type = $validated['type'];
        $name = trim($validated['name']);
        $parentId = $validated['parent_id'] ?? null;

        if ($type === 'subject') {
            $slug = $this->slugService->unique(Subject::class, $name);
            $item = Subject::query()->create([
                'class_id' => $parentId,
                'name' => $name,
                'slug' => $slug,
                'status' => TaxonomyStatus::Active,
                'sort_order' => 0,
            ]);
        } elseif ($type === 'class') {
            $slug = $this->slugService->unique(SchoolClass::class, $name);
            $item = SchoolClass::query()->create([
                'category_id' => $parentId,
                'name' => $name,
                'slug' => $slug,
                'status' => TaxonomyStatus::Active,
                'sort_order' => 0,
            ]);
        } else {
            $slug = $this->slugService->unique(Category::class, $name);
            $item = Category::query()->create([
                'tuition_type_id' => $parentId,
                'name' => $name,
                'slug' => $slug,
                'status' => TaxonomyStatus::Active,
                'sort_order' => 0,
            ]);
        }

        return response()->json([
            'message' => 'Taxonomy item created successfully.',
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
            ],
        ], 201);
    }
}

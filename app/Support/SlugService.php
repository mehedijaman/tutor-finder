<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SlugService
{
    /**
     * Create a unique slug for a model class.
     *
     * @param  class-string<Model>  $modelClass
     */
    public function unique(
        string $modelClass,
        string $base,
        int|string|null $ignoreId = null,
        bool $withTrashed = true,
    ): string {
        $normalizedBase = Str::slug($base);

        if ($normalizedBase === '') {
            $normalizedBase = 'item';
        }

        $slug = $normalizedBase;
        $suffix = 2;

        while ($this->slugExists($modelClass, $slug, $ignoreId, $withTrashed)) {
            $slug = "{$normalizedBase}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * Determine whether a slug already exists for the given model class.
     *
     * @param  class-string<Model>  $modelClass
     */
    protected function slugExists(
        string $modelClass,
        string $slug,
        int|string|null $ignoreId,
        bool $withTrashed,
    ): bool {
        $query = $modelClass::query();

        if ($withTrashed && $this->usesSoftDeletes($modelClass)) {
            $query->withTrashed();
        }

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query
            ->where('slug', $slug)
            ->exists();
    }

    /**
     * Determine whether the model class uses soft deletes.
     *
     * @param  class-string<Model>  $modelClass
     */
    protected function usesSoftDeletes(string $modelClass): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($modelClass), true);
    }
}

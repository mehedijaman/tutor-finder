<?php

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TuitionType;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;

it('admin can create all tuition taxonomy resources', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->post(route('admin.tuition.taxonomies.countries.store'), [
            'name' => 'Bangladesh',
            'slug' => '',
            'status' => 'active',
        ])
        ->assertRedirect(route('admin.tuition.taxonomies.countries.index', absolute: false));

    $country = Country::query()->where('name', 'Bangladesh')->firstOrFail();

    $this->actingAs($admin)
        ->post(route('admin.tuition.taxonomies.cities.store'), [
            'country_id' => $country->id,
            'name' => 'Dhaka',
            'slug' => '',
            'status' => 'active',
        ])
        ->assertRedirect(route('admin.tuition.taxonomies.cities.index', absolute: false));

    $city = City::query()->where('name', 'Dhaka')->firstOrFail();

    $this->actingAs($admin)
        ->post(route('admin.tuition.taxonomies.areas.store'), [
            'city_id' => $city->id,
            'name' => 'Dhanmondi',
            'slug' => '',
            'status' => 'active',
        ])
        ->assertRedirect(route('admin.tuition.taxonomies.areas.index', absolute: false));

    $this->actingAs($admin)
        ->post(route('admin.tuition.taxonomies.categories.store'), [
            'name' => 'English Medium',
            'slug' => '',
            'description' => 'Academic category',
            'status' => 'active',
            'sort_order' => 1,
        ])
        ->assertRedirect(route('admin.tuition.taxonomies.categories.index', absolute: false));

    $category = Category::query()->where('name', 'English Medium')->firstOrFail();

    $this->actingAs($admin)
        ->post(route('admin.tuition.taxonomies.classes.store'), [
            'category_id' => $category->id,
            'name' => 'Class 10',
            'slug' => '',
            'status' => 'active',
            'sort_order' => 2,
        ])
        ->assertRedirect(route('admin.tuition.taxonomies.classes.index', absolute: false));

    $schoolClass = SchoolClass::query()->where('name', 'Class 10')->firstOrFail();

    $this->actingAs($admin)
        ->post(route('admin.tuition.taxonomies.subjects.store'), [
            'class_id' => $schoolClass->id,
            'name' => 'Physics',
            'slug' => '',
            'status' => 'active',
            'sort_order' => 3,
        ])
        ->assertRedirect(route('admin.tuition.taxonomies.subjects.index', absolute: false));

    $this->actingAs($admin)
        ->post(route('admin.tuition.taxonomies.tuition-types.store'), [
            'name' => 'Home Tuition',
            'slug' => '',
            'description' => 'At home support',
            'status' => 'active',
            'sort_order' => 4,
        ])
        ->assertRedirect(route('admin.tuition.taxonomies.tuition-types.index', absolute: false));

    $this->assertDatabaseHas('countries', ['name' => 'Bangladesh', 'slug' => 'bangladesh']);
    $this->assertDatabaseHas('cities', ['name' => 'Dhaka', 'slug' => 'dhaka']);
    $this->assertDatabaseHas('areas', ['name' => 'Dhanmondi', 'slug' => 'dhanmondi']);
    $this->assertDatabaseHas('categories', ['name' => 'English Medium', 'slug' => 'english-medium']);
    $this->assertDatabaseHas('classes', ['name' => 'Class 10', 'slug' => 'class-10']);
    $this->assertDatabaseHas('subjects', ['name' => 'Physics', 'slug' => 'physics']);
    $this->assertDatabaseHas('tuition_types', ['name' => 'Home Tuition', 'slug' => 'home-tuition']);
});

it('enforces scoped uniqueness and slug behavior including soft deleted records', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $countryA = Country::factory()->create(['name' => 'Country A', 'slug' => 'country-a']);
    $countryB = Country::factory()->create(['name' => 'Country B', 'slug' => 'country-b']);

    $this->actingAs($admin)
        ->post(route('admin.tuition.taxonomies.cities.store'), [
            'country_id' => $countryA->id,
            'name' => 'Metro',
            'slug' => '',
            'status' => 'active',
        ])
        ->assertRedirect();

    $this->actingAs($admin)
        ->post(route('admin.tuition.taxonomies.cities.store'), [
            'country_id' => $countryB->id,
            'name' => 'Metro',
            'slug' => '',
            'status' => 'active',
        ])
        ->assertRedirect();

    $citySlugA = City::query()->where('country_id', $countryA->id)->where('name', 'Metro')->firstOrFail()->slug;
    $citySlugB = City::query()->where('country_id', $countryB->id)->where('name', 'Metro')->firstOrFail()->slug;

    expect($citySlugA)->toBe('metro');
    expect($citySlugB)->toBe('metro');

    $this->actingAs($admin)
        ->post(route('admin.tuition.taxonomies.countries.store'), [
            'name' => 'Alpha',
            'slug' => '',
            'status' => 'active',
        ])
        ->assertRedirect();

    $alpha = Country::query()->where('name', 'Alpha')->firstOrFail();

    $this->actingAs($admin)
        ->delete(route('admin.tuition.taxonomies.countries.destroy', $alpha))
        ->assertRedirect();

    $this->actingAs($admin)
        ->post(route('admin.tuition.taxonomies.countries.store'), [
            'name' => 'Alpha Duplicate',
            'slug' => 'alpha',
            'status' => 'active',
        ])
        ->assertRedirect();

    $slugs = Country::withTrashed()
        ->whereIn('name', ['Alpha', 'Alpha Duplicate'])
        ->orderBy('id')
        ->pluck('slug')
        ->all();

    expect($slugs)->toBe(['alpha', 'alpha-2']);
});

it('blocks parent deletes according to child matrix and supports restore and force delete flow', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $country = Country::factory()->create();
    $city = City::factory()->create(['country_id' => $country->id]);

    $this->actingAs($admin)
        ->delete(route('admin.tuition.taxonomies.countries.destroy', $country))
        ->assertSessionHasErrors('country');

    $this->actingAs($admin)
        ->delete(route('admin.tuition.taxonomies.cities.destroy', $city))
        ->assertRedirect();

    $this->actingAs($admin)
        ->delete(route('admin.tuition.taxonomies.countries.destroy', $country))
        ->assertRedirect();

    expect($country->refresh()->trashed())->toBeTrue();

    $this->actingAs($admin)
        ->delete(route('admin.tuition.taxonomies.countries.force-delete', $country->id))
        ->assertSessionHasErrors('country');

    $this->actingAs($admin)
        ->delete(route('admin.tuition.taxonomies.cities.force-delete', $city->id))
        ->assertRedirect();

    $this->actingAs($admin)
        ->delete(route('admin.tuition.taxonomies.countries.force-delete', $country->id))
        ->assertRedirect();

    expect(Country::withTrashed()->find($country->id))->toBeNull();
    expect(City::withTrashed()->find($city->id))->toBeNull();
});

it('validates parent-child relationships and restore lifecycle', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->post(route('admin.tuition.taxonomies.cities.store'), [
            'country_id' => 999999,
            'name' => 'Ghost City',
            'slug' => '',
            'status' => 'active',
        ])
        ->assertSessionHasErrors('country_id');

    $category = Category::factory()->create();
    $schoolClass = SchoolClass::factory()->create(['category_id' => $category->id]);
    $subject = Subject::factory()->create(['class_id' => $schoolClass->id]);

    $this->actingAs($admin)
        ->delete(route('admin.tuition.taxonomies.subjects.destroy', $subject))
        ->assertRedirect();

    $this->actingAs($admin)
        ->patch(route('admin.tuition.taxonomies.subjects.restore', $subject->id))
        ->assertRedirect();

    expect($subject->refresh()->trashed())->toBeFalse();

    $tuitionType = TuitionType::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.tuition.taxonomies.tuition-types.destroy', $tuitionType))
        ->assertRedirect();

    $this->actingAs($admin)
        ->patch(route('admin.tuition.taxonomies.tuition-types.restore', $tuitionType->id))
        ->assertRedirect();

    $this->actingAs($admin)
        ->delete(route('admin.tuition.taxonomies.tuition-types.destroy', $tuitionType))
        ->assertRedirect();

    $this->actingAs($admin)
        ->delete(route('admin.tuition.taxonomies.tuition-types.empty-recycle-bin'))
        ->assertRedirect();

    expect(TuitionType::withTrashed()->find($tuitionType->id))->toBeNull();
});

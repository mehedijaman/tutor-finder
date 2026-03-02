<?php

use App\Models\Category;
use App\Models\Country;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TuitionType;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;

it('forbids admin without permissions from tuition taxonomy management routes', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();

    $country = Country::factory()->create();
    $category = Category::factory()->create();
    $schoolClass = SchoolClass::factory()->create(['category_id' => $category->id]);
    $subject = Subject::factory()->create(['class_id' => $schoolClass->id]);
    $tuitionType = TuitionType::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.tuition.taxonomies.countries.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('admin.tuition.taxonomies.cities.store'), [
            'country_id' => $country->id,
            'name' => 'Unauthorized City',
            'slug' => '',
            'status' => 'active',
        ])
        ->assertForbidden();

    $this->actingAs($admin)
        ->patch(route('admin.tuition.taxonomies.categories.status', $category), [
            'status' => 'inactive',
        ])
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('admin.tuition.taxonomies.classes.destroy', $schoolClass))
        ->assertForbidden();

    $this->actingAs($admin)
        ->patch(route('admin.tuition.taxonomies.subjects.restore', $subject->id))
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('admin.tuition.taxonomies.tuition-types.force-delete', $tuitionType->id))
        ->assertForbidden();
});

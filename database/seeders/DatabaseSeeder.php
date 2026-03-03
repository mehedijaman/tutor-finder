<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminRolesAndPermissionsSeeder::class,
            InitialAdminSeeder::class,
            SiteSettingsSeeder::class,
            DemoDataSeeder::class,
            BlogDemoDataSeeder::class,
            TestimonialFaqContactSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+15550000001',
            'role' => 'guardian',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);
    }
}

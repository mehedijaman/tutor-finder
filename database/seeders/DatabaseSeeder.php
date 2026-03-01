<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminRolesAndPermissionsSeeder::class,
            InitialAdminSeeder::class,
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

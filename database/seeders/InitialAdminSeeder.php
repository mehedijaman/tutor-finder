<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => env('INITIAL_ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => env('INITIAL_ADMIN_NAME', 'Super Admin'),
                'password' => Hash::make(env('INITIAL_ADMIN_PASSWORD', 'password')),
                'role' => 'admin',
                'status' => 'active',
                'phone' => null,
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
            ]
        );

        if ($admin->role !== 'admin') {
            $admin->forceFill([
                'role' => 'admin',
                'status' => 'active',
            ])->save();
        }

        $admin->assignRole('super-admin');
    }
}

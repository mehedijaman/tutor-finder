<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BangladeshDemoUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        $guardians = [
            ['name' => 'Fatema Rahman', 'email' => 'fatema.rahman.guardian@example.com', 'phone' => '+8801711000001'],
            ['name' => 'Mahmud Hasan', 'email' => 'mahmud.hasan.guardian@example.com', 'phone' => '+8801711000002'],
            ['name' => 'Nusrat Jahan', 'email' => 'nusrat.jahan.guardian@example.com', 'phone' => '+8801711000003'],
            ['name' => 'Farhana Akter', 'email' => 'farhana.akter.guardian@example.com', 'phone' => '+8801711000004'],
            ['name' => 'Sabbir Ahmed', 'email' => 'sabbir.ahmed.guardian@example.com', 'phone' => '+8801711000005'],
            ['name' => 'Tanvir Hossain', 'email' => 'tanvir.hossain.guardian@example.com', 'phone' => '+8801711000006'],
            ['name' => 'Sadia Islam', 'email' => 'sadia.islam.guardian@example.com', 'phone' => '+8801711000007'],
            ['name' => 'Kamrul Hassan', 'email' => 'kamrul.hassan.guardian@example.com', 'phone' => '+8801711000008'],
        ];

        $tutors = [
            ['name' => 'Sayem Karim', 'email' => 'sayem.karim.tutor@example.com', 'phone' => '+8801812000001'],
            ['name' => 'Adiba Noor', 'email' => 'adiba.noor.tutor@example.com', 'phone' => '+8801812000002'],
            ['name' => 'Naim Chowdhury', 'email' => 'naim.chowdhury.tutor@example.com', 'phone' => '+8801812000003'],
            ['name' => 'Rifat Haque', 'email' => 'rifat.haque.tutor@example.com', 'phone' => '+8801812000004'],
            ['name' => 'Mehjabin Sultana', 'email' => 'mehjabin.sultana.tutor@example.com', 'phone' => '+8801812000005'],
            ['name' => 'Asif Rahman', 'email' => 'asif.rahman.tutor@example.com', 'phone' => '+8801812000006'],
            ['name' => 'Saima Yasmin', 'email' => 'saima.yasmin.tutor@example.com', 'phone' => '+8801812000007'],
            ['name' => 'Faisal Kabir', 'email' => 'faisal.kabir.tutor@example.com', 'phone' => '+8801812000008'],
        ];

        foreach ($guardians as $guardian) {
            $this->upsertUser($guardian, 'guardian', $password);
        }

        foreach ($tutors as $tutor) {
            $this->upsertUser($tutor, 'tutor', $password);
        }
    }

    /**
     * Create or update a demo user record.
     *
     * @param  array{name: string, email: string, phone: string}  $attributes
     */
    private function upsertUser(array $attributes, string $role, string $password): void
    {
        $user = User::query()->withTrashed()->firstOrNew([
            'email' => $attributes['email'],
        ]);

        $user->fill([
            'name' => $attributes['name'],
            'phone' => $attributes['phone'],
            'password' => $password,
            'role' => $role,
            'status' => 'active',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ])->save();

        $this->restoreIfTrashed($user);
    }

    /**
     * Restore a soft-deleted model instance when found in trashed state.
     */
    private function restoreIfTrashed(Model $model): void
    {
        if (method_exists($model, 'trashed') && $model->trashed()) {
            $model->restore();
        }
    }
}

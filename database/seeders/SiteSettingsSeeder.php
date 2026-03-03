<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SiteSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'site_name' => 'Tutor Finder',
                'slogan' => 'Explore for Excellence',
                'phone_numbers' => ['01947-368456'],
                'emails' => ['utorfinder14@gmail.com'],
                'addresses' => [
                    [
                        'label' => 'Office',
                        'address' => 'Anarkoli Surper Market (3rd floor) Siddheswari, 1217',
                        'map_url' => null,
                    ],
                ],
                'social_details' => [
                    'facebook' => 'https://www.facebook.com/tutorfinder.official',
                ],
            ],
        );
    }
}

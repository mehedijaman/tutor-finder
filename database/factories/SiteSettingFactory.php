<?php

namespace Database\Factories;

use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_name' => fake()->company(),
            'slogan' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'logo_path' => null,
            'phone_numbers' => [
                fake()->e164PhoneNumber(),
                fake()->e164PhoneNumber(),
            ],
            'emails' => [
                fake()->safeEmail(),
                fake()->safeEmail(),
            ],
            'addresses' => [
                [
                    'label' => 'Head Office',
                    'address' => fake()->address(),
                    'map_url' => fake()->url(),
                ],
            ],
            'social_details' => [
                'facebook' => fake()->url(),
                'youtube' => fake()->url(),
            ],
            'trade_licence_no' => fake()->numerify('TL-#######'),
            'tin_no' => fake()->numerify('TIN-########'),
            'bin_no' => fake()->numerify('BIN-########'),
        ];
    }
}

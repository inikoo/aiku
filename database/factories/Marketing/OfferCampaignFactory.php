<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 27 Apr 2023 16:51:53 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Factories\Marketing;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deals\OfferCampaign>
 */
class OfferCampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'code' => fake()->lexify,
            'name' => fake()->name
        ];
    }
}

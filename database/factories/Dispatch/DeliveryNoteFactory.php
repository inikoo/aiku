<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 27 Apr 2023 16:51:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Factories\Dispatch;

use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dispatching\Shipper>
 */
class DeliveryNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'number' => fake()->numberBetween(100, 999),
            'state'  => DeliveryNoteStateEnum::PACKING,
            'status' => DeliveryNoteStatusEnum::HANDLING,
            'email'  => fake()->email,
            'date'   => fake()->date
        ];
    }
}

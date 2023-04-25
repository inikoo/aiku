<?php

namespace Database\Factories\Helpers;

use App\Models\Assets\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Helpers\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $country = Country::latest()->first();

        return [
            'address_line_1'      => fake()->address,
            'address_line_2'      => fake()->address,
            'sorting_code'        => '12-34-56',
            'postal_code'         => fake()->postcode,
            'locality'            => fake()->locale,
            'dependant_locality'  => 'Hometown',
            'administrative_area' => 'Apartment',
            'country_id'          => $country->id
        ];
    }
}

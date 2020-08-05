<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Customer;
use Faker\Generator as Faker;

$factory->define(Customer::class, function (Faker $faker) {

    $name=$faker->name;

    return [
        'name'      => $name,
        'slug'      => Str::slug($name),
    ];
});

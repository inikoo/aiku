<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(Model::class, function (Faker $faker) {
     
    $name = $faker->word;
    return [
        'tenant_id' => $tenant_id,
        'name'      => $name,
        'slug'      => Str::slug($name),

    ];
});

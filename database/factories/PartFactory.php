<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Part;
use Faker\Generator as Faker;


$factory->define(Part::class, function (Faker $faker, $args) {

   
    $name = $faker->word;
    return [
        'tenant_id' => $args['tenant_id'],
     
        'slug'      => Str::slug($name),

    ];
});

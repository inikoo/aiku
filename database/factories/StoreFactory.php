<?php
<<<<<<< Updated upstream
/*
Author: Amila
Created:  Mon Aug 03 2020 15:01:44 GMT+0800 (Malaysia Time) Kuala Lumpur, Malaysia
Copyright (c) 2020,  AIku.io

Version 4
*/

=======
>>>>>>> Stashed changes

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Store;
<<<<<<< Updated upstream
use Faker\Generator as Faker;


$factory->define(Store::class, function (Faker $faker, $args) {

   
    $name = $faker->word;
    return [
        'tenant_id' => $args['tenant_id'],
     
=======
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Store::class, function (Faker $faker,$tenant_id) {
   
    $name = $faker->word;

    return [
        'tenant_id' => $tenant_id,
        'name'      => $name,
>>>>>>> Stashed changes
        'slug'      => Str::slug($name),

    ];
});

<?php
/*
Author: Amila
Created:  Mon Aug 03 2020 15:01:44 GMT+0800 (Malaysia Time) Kuala Lumpur, Malaysia
Copyright (c) 2020,  AIku.io

Version 4
*/


/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Store;
use Faker\Generator as Faker;



$factory->define(Store::class, function (Faker $faker, $tenant_id) {

    $name = $faker->word;

    return [
        'tenant_id' => $tenant_id,
        'name'      => $name,
        'slug'      => Str::slug($name),

    ];
});

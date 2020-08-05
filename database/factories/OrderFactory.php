<?php
/*
Author: amila
Created:  Mon Aug 03 2020 15:01:44 GMT+0800 (Malaysia Time) Kuala Lumpur, Malaysia
Copyright (c) 2020,  AIku.io

Version 4
*/


/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Order;
use Faker\Generator as Faker;
 
$factory->define(Order::class, function (Faker $faker) {

   
    $name = $faker->word;
    return [
        'tenant_id' => $tenant_id,
        'name'      => $name,
        'slug'      => Str::slug($name),

    ];
});

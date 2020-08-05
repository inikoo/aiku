<?php
/*
Author: amila
Created:  Mon Aug 03 2020 15:01:44 GMT+0800 (Malaysia Time) Kuala Lumpur, Malaysia
Copyright (c) 2020,  AIku.io

Version 4
*/


/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Order;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
 
$factory->define(Order::class, function (Faker $faker, $args) {

   
    $name = $faker->word;
    return [
        'tenant_id' => $args['tenant_id'],
     
        'slug'      => Str::slug($name),

    ];
});

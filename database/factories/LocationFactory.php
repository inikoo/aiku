<?php
/*
Author: Raul A Perusquía-Flores (raul@inikoo.com)
Created:  Mon Aug 03 2020 09:46:51 GMT+0800 (Malaysia Time) Kuala Lumpur, Malaysia
Copyright (c) 2020,  AIku.io

Version 4
*/


/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Distribution\Location;
use Illuminate\Support\Str;
use Faker\Generator as Faker;


$factory->define(
    Location::class, function (Faker $faker, $args) {


    return [
        'tenant_id' => $args['tenant_id'],

        'code' => Str::slug($faker->uuid),

    ];
}
);

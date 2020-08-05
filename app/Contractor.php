<?php
/*
Author: Raul A Perusquía-Flores (raul@inikoo.com)
Created:  Mon Aug 03 2020 15:22:26 GMT+0800 (Malaysia Time) Kuala Lumpur, Malaysia
Copyright (c) 2020,  AIku.io

Version 4
*/




namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Contractor
 *
 * @property-read \App\User|null $image
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Contractor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Contractor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Contractor query()
 * @mixin \Eloquent
 */
class Contractor extends Model {
    use UsesTenantConnection;

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    public function image()
    {
        return $this->morphOne('App\User', 'userable');
    }



}

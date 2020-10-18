<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sun, 18 Oct 2020 18:41:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Helpers;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandLordConnection;

/**
 *
 * @property int    $id
 * @property string $created_at
 * @property int    $legacy_id
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Tax extends Model {
    use UsesLandLordConnection,Sluggable;

    protected $casts = [
        'data' => 'array'
    ];
    protected $attributes = [
        'data'     => '{}',
    ];

    protected $guarded=[];

    public function sluggable() {
        return [
            'slug' => [
                'source'   => 'sluggledCode',
                'onUpdate' => true
            ]
        ];
    }

    public function getSluggledCodeAttribute() {


        return $this->country_code.'-'.$this->code;
    }


}

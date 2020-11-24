<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 17 Nov 2020 14:08:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\ECommerce;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;



class Webpage extends Model implements Auditable{
    use UsesTenantConnection,Sluggable;
    use \OwenIt\Auditing\Auditable;

        protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
        'settings' => '{}'
    ];

    protected $guarded = [];

    function sluggable() {
        return [
            'slug' => [
                'source'   => 'sluggledCode',
                'onUpdate' => true
            ]
        ];
    }

    function getSluggledCodeAttribute() {
        return $this->path.' '.$this->website->slug;
    }

    public function website() {
        return $this->belongsTo('App\Models\ECommerce\Website')->withTrashed();
    }


    public function webBlocks() {
        return $this->hasMany('App\Models\ECommerce\WebBlock');
    }


}

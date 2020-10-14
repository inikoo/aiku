<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 15 Oct 2020 00:35:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 *
 * @property int    $id
 * @property string $deleted_at
 * @property array  $data
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class CustomerClient extends Model implements Auditable {
    use UsesTenantConnection, Sluggable;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $casts = [
        'data' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function sluggable() {
        return [
            'slug' => [
                'source'   => 'sluggledName',
                'onUpdate' => true
            ]
        ];
    }

    public function getSluggledNameAttribute() {

        if ($this->code != '') {
            return $this->code;
        }

        $sluggableName = au_escape_slug($this->name);
        if ($sluggableName == '') {
            $sluggableName = 'empty';
        }

        return $sluggableName;
    }


    public function customer() {
        return $this->belongsTo('App\Models\CRM\Customer');
    }


    public function deliveryAddress() {
        return $this->belongsTo('App\Models\Helpers\Address');
    }


}

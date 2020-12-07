<?php
/*
Author: Raul Perusquia (raul@inikoo.com)
Created:  Tue Jul 28 2020 20:24:02 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, Raúl Alejandro Perusquía Flores

Version 4
*/


namespace App\Models\Suppliers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;


/**
 * App\Models\Suppliers\Supplier
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $owner_type
 * @property int $owner_id
 * @property array $settings
 * @property array $data

 */
class Supplier extends Model {
        protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
        'settings' => '{}'
    ];


    public function tenants(): BelongsToMany {
        return $this->belongsToMany('App\Tenant')->withTimestamps();
    }

    public function agent(): BelongsTo {
        return $this->belongsTo('App\Models\Suppliers\Agent');
    }

    public function owner(): MorphTo {
        return $this->morphTo();
    }

    public function supplierOwner(): MorphOne {
        return $this->morphOne('App\Models\Suppliers\Supplier', 'owner');
    }
}

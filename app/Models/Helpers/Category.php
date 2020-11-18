<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Wed, 18 Nov 2020 14:20:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Kalnoy\Nestedset\NodeTrait;


class Category extends Model implements Auditable {
    use UsesTenantConnection, SoftDeletes, NodeTrait;
    use \OwenIt\Auditing\Auditable;


    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}'
    ];

    protected $guarded = [];

    public function stocks() {
        return $this->morphedByMany('App\Models\Distribution\Stock', 'categoriable')->withTimestamps();
    }

    public function customers() {
        return $this->morphedByMany('App\Models\CRM\Customer', 'categoriable')->withTimestamps();
    }

    public function products() {
        return $this->morphedByMany('App\Models\Stores\Product', 'categoriable')->withTimestamps();
    }

    public function families() {
        return $this->morphedByMany('App\Models\Helpers\Category', 'categoriable')->withTimestamps();
    }

    public function invoices() {
        return $this->morphedByMany('App\Models\Sales\Invoice', 'categoriable')->withTimestamps();
    }




}

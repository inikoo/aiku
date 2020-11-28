<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 20 Nov 2020 01:24:03 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


class Prospect extends Model {
    use UsesTenantConnection;

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}'
    ];

    protected $guarded = [];

    public function store() {
        return $this->belongsTo('App\Models\Stores\Store');
    }

    public function customer() {
        return $this->belongsTo('App\Models\CRM\Customer')->withTrashed();
    }

    public function employees() {
        return $this->belongsToMany('App\Models\HR\Employee')->withTimestamps()->withPivot(['allocation']);
    }
}

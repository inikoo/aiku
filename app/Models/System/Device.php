<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 29 Sep 2020 18:18:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * Class Device
 *
 * @package App\Models\System
 *
 * @property $id integer                                                                                                         $password
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Device extends Model {
    use UsesTenantConnection;

    protected $guarded = [
    ];



    public function user() {
        return $this->belongsTo('App\Models\User');
    }


}

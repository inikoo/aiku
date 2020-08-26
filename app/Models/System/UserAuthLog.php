<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Wed, 26 Aug 2020 12:04:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class UserAuthLog extends Model {
    use UsesTenantConnection;


    public function user() {
        return $this->belongsTo('\App\User');
    }


}

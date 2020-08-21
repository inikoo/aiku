<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sat, 22 Aug 2020 00:04:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class ClockingNfcTag extends Model {
    use UsesTenantConnection;

    public function employee() {
        return $this->belongsTo('App\Models\HR\Employee');
    }
}

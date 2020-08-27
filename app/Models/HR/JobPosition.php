<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 18 Aug 2020 19:33:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class JobPosition extends Model implements Auditable {
    use UsesTenantConnection;
    use \OwenIt\Auditing\Auditable;


    protected $casts = [
        'data'     => 'array'
    ];

    protected $attributes = [
        'data'     => '{}',
    ];




}

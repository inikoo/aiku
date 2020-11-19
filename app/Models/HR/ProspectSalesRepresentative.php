<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 20 Nov 2020 04:24:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 *
 * @property int    $id


 */
class ProspectSalesRepresentative extends Model {
    use UsesTenantConnection;

    protected $table = 'prospect_sales_representatives';

    protected $guarded =[];


}

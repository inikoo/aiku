<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 May 2023 15:26:45 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models;

use App\Models\Traits\UsesGroupConnection;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\SupplierProductTenant
 *
 * @property int $id
 * @property int $supplier_product_id
 * @property int $tenant_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $source_id
 * @method static \Illuminate\Database\Eloquent\Builder|SupplierProductTenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SupplierProductTenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SupplierProductTenant query()
 * @mixin \Eloquent
 */
class SupplierProductTenant extends Pivot
{
    use UsesGroupConnection;
    protected $table = 'supplier_product_tenant';
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 16:09:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Models\Traits\UsesGroupConnection;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * App\Models\Procurement\SupplierProductTenant
 *
 * @property int $id
 * @property int $supplier_product_id
 * @property int $tenant_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $source_id
 * @method static Builder|SupplierProductTenant newModelQuery()
 * @method static Builder|SupplierProductTenant newQuery()
 * @method static Builder|SupplierProductTenant query()
 * @mixin Eloquent
 */
class SupplierProductTenant extends Pivot
{
    use UsesGroupConnection;
    protected $table = 'supplier_product_tenant';
}

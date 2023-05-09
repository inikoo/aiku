<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 16:09:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Models\Traits\UsesGroupConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\Procurement\SupplierTenant
 *
 * @property int $id
 * @property int $supplier_id
 * @property int $tenant_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $source_id
 * @property-read \App\Models\Procurement\Supplier $supplier
 * @method static Builder|SupplierTenant newModelQuery()
 * @method static Builder|SupplierTenant newQuery()
 * @method static Builder|SupplierTenant query()
 * @mixin \Eloquent
 */
class SupplierTenant extends Pivot
{
    use UsesGroupConnection;

    protected $table = 'supplier_tenant';


    protected $guarded = [];


    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 16:09:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Enums\Procurement\SupplierOrganisation\SupplierOrganisationStatusEnum;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\Procurement\SupplierOrganisation
 *
 * @property SupplierOrganisationStatusEnum $status
 * @property-read \App\Models\Procurement\Supplier|null $supplier
 * @method static Builder|SupplierOrganisation newModelQuery()
 * @method static Builder|SupplierOrganisation newQuery()
 * @method static Builder|SupplierOrganisation query()
 * @mixin Eloquent
 */
class SupplierOrganisation extends Pivot
{
    protected $table = 'supplier_tenant';

    protected $casts = [
        'status' => SupplierOrganisationStatusEnum::class
    ];


    protected $guarded = [];


    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

}

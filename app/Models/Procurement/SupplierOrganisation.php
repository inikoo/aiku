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
use Illuminate\Support\Carbon;

/**
 * App\Models\Procurement\SupplierOrganisation
 *
 * @property int $id
 * @property int $supplier_id
 * @property int $organisation_id
 * @property int|null $agent_id
 * @property SupplierOrganisationStatusEnum $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $source_id
 * @property-read \App\Models\Procurement\Supplier $supplier
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

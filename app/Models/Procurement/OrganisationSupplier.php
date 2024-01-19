<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 16:09:05 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Models\SupplyChain\Supplier;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\Procurement\OrganisationSupplier
 *
 * @property int $id
 * @property int $supplier_id
 * @property int $organisation_id
 * @property int|null $agent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $source_id
 * @property-read Supplier $supplier
 * @method static Builder|OrganisationSupplier newModelQuery()
 * @method static Builder|OrganisationSupplier newQuery()
 * @method static Builder|OrganisationSupplier query()
 * @mixin Eloquent
 */
class OrganisationSupplier extends Pivot
{
    protected $table = 'organisation_supplier';

    protected $guarded = [];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

}

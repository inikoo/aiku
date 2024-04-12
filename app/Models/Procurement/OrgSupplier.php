<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 16:09:05 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Procurement\OrgSupplier
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $supplier_id
 * @property int|null $agent_id
 * @property int|null $org_agent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $source_id
 * @property-read Group $group
 * @property-read Organisation $organisation
 * @property-read OrgSupplierStats|null $stats
 * @property-read Supplier $supplier
 * @method static Builder|OrgSupplier newModelQuery()
 * @method static Builder|OrgSupplier newQuery()
 * @method static Builder|OrgSupplier query()
 * @mixin Eloquent
 */
class OrgSupplier extends Model
{
    protected $table = 'org_suppliers';

    protected $guarded = [];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(OrgSupplierStats::class);
    }

}

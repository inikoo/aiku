<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jul 2024 13:54:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $stored_item_audit_id
 * @property int $pallet_id
 * @property int $stored_item_id
 * @property string|null $audited_at
 * @property string|null $original_quantity
 * @property string $audited_quantity
 * @property string|null $state
 * @property string|null $audit_type
 * @property string|null $reason
 * @property string $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItemAuditDelta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItemAuditDelta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItemAuditDelta query()
 * @mixin \Eloquent
 */
class StoredItemAuditDelta extends Model
{
    protected $guarded = [];
}
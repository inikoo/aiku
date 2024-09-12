<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 22:27:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Dispatching;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Dispatching\Picking
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property int $delivery_note_id
 * @property int $delivery_note_item_id
 * @property bool $status
 * @property string $state
 * @property string $outcome
 * @property int|null $org_stock_movement_id
 * @property int $org_stock_id
 * @property int|null $picker_id
 * @property int|null $packer_id
 * @property string|null $vessel_picking
 * @property string|null $vessel_packing
 * @property int|null $location_id
 * @property string $data
 * @property string|null $picker_assigned_at
 * @property string|null $picking_at
 * @property string|null $picked_at
 * @property string|null $packer_assigned_at
 * @property string|null $packing_at
 * @property string|null $packed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Picking newModelQuery()
 * @method static Builder|Picking newQuery()
 * @method static Builder|Picking query()
 * @mixin Eloquent
 */
class Picking extends Model
{
}

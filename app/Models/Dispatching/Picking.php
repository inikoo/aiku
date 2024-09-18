<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 22:27:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Dispatching;

use App\Enums\Dispatching\Picking\PickingOutcomeEnum;
use App\Enums\Dispatching\Picking\PickingStateEnum;
use App\Enums\Dispatching\Picking\PickingVesselEnum;
use App\Models\Traits\InShop;
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
 * @property PickingStateEnum $state
 * @property PickingOutcomeEnum $outcome
 * @property string $quantity_required
 * @property string|null $quantity_picked
 * @property string|null $quantity_packed
 * @property string|null $quantity_dispatched
 * @property int|null $org_stock_movement_id
 * @property int $org_stock_id
 * @property int|null $picker_id
 * @property int|null $packer_id
 * @property PickingVesselEnum|null $vessel_picking
 * @property PickingVesselEnum|null $vessel_packing
 * @property int|null $location_id
 * @property array $data
 * @property string|null $picker_assigned_at
 * @property string|null $picking_at
 * @property string|null $picked_at
 * @property string|null $packer_assigned_at
 * @property string|null $packing_at
 * @property string|null $packed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Catalogue\Shop $shop
 * @method static Builder|Picking newModelQuery()
 * @method static Builder|Picking newQuery()
 * @method static Builder|Picking query()
 * @mixin Eloquent
 */
class Picking extends Model
{
    use InShop;

    protected $casts = [
        'data'              => 'array',
        'state'             => PickingStateEnum::class,
        'outcome'           => PickingOutcomeEnum::class,
        'vessel_picking'    => PickingVesselEnum::class,
        'vessel_packing'    => PickingVesselEnum::class
    ];

    protected $guarded = [];

    protected $attributes = [
        'data' => '{}',
    ];
}

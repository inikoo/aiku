<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 03 Dec 2024 15:12:11 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Dispatching;

use App\Enums\Dispatching\Packing\PackingEngineEnum;
use App\Models\SysAdmin\User;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property int $delivery_note_id
 * @property int $delivery_note_item_id
 * @property int|null $picking_id
 * @property bool $status 0: pending, 1: done
 * @property bool $is_blocked
 * @property string|null $quantity_packed
 * @property int|null $packer_id
 * @property PackingEngineEnum $engine
 * @property array $data
 * @property string|null $packed_at
 * @property string|null $picking_blocked_at
 * @property string|null $done_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Dispatching\DeliveryNoteItem $deliveryNoteItem
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read User|null $packer
 * @property-read \App\Models\Dispatching\Picking|null $picking
 * @property-read \App\Models\Catalogue\Shop $shop
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Packing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Packing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Packing query()
 * @mixin \Eloquent
 */
class Packing extends Model
{
    use InShop;

    protected $casts = [
        'data'              => 'array',
        'engine'            => PackingEngineEnum::class,
    ];

    protected $guarded = [];

    protected $attributes = [
        'data' => '{}',
    ];

    public function picking(): BelongsTo
    {
        return $this->belongsTo(Picking::class);
    }

    public function deliveryNoteItem(): BelongsTo
    {
        return $this->belongsTo(DeliveryNoteItem::class);
    }

    public function packer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packer_id');
    }


}

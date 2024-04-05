<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 22:27:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Dispatch;

use App\Enums\Dispatch\DeliveryNoteItem\DeliveryNoteItemStateEnum;
use App\Enums\Dispatch\DeliveryNoteItem\DeliveryNoteItemStatusEnum;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Dispatch\DeliveryNoteItem
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $delivery_note_id
 * @property int $stock_family_id
 * @property int $stock_id
 * @property int $org_stock_family_id
 * @property int $org_stock_id
 * @property int $transaction_id
 * @property int|null $picking_id
 * @property DeliveryNoteItemStateEnum $state
 * @property DeliveryNoteItemStatusEnum $status
 * @property string $quantity_required
 * @property string|null $quantity_picked
 * @property string|null $quantity_packed
 * @property string|null $quantity_dispatched
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @method static Builder|DeliveryNoteItem newModelQuery()
 * @method static Builder|DeliveryNoteItem newQuery()
 * @method static Builder|DeliveryNoteItem onlyTrashed()
 * @method static Builder|DeliveryNoteItem query()
 * @method static Builder|DeliveryNoteItem withTrashed()
 * @method static Builder|DeliveryNoteItem withoutTrashed()
 * @mixin Eloquent
 */
class DeliveryNoteItem extends Model
{
    use SoftDeletes;


    protected $table = 'delivery_note_items';

    protected $casts = [
        'data'   => 'array',
        'state'  => DeliveryNoteItemStateEnum::class,
        'status' => DeliveryNoteItemStatusEnum::class,

    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    //    public function pickings(): BelongsToMany
    //    {
    //        return $this->belongsToMany(Picking::class)->withTimestamps();
    //    }
}

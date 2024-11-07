<?php
/*
 * author Arya Permana - Kirin
 * created on 01-11-2024-11h-30m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Ordering;

use App\Enums\Ordering\Purge\PurgedOrderStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $purge_id
 * @property int $order_id
 * @property PurgedOrderStatusEnum $status
 * @property string|null $purged_at
 * @property string|null $order_last_updated_at
 * @property string $amount
 * @property string $org_amount
 * @property string $grp_amount
 * @property int $number_transactions
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ordering\Order|null $order
 * @property-read \App\Models\Ordering\Purge $purge
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurgedOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurgedOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurgedOrder query()
 * @mixin \Eloquent
 */
class PurgedOrder extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status'       => PurgedOrderStatusEnum::class,
    ];

    public function purge(): BelongsTo
    {
        return $this->belongsTo(Purge::class, 'purge_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }



}

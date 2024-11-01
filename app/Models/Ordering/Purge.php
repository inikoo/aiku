<?php
/*
 * author Arya Permana - Kirin
 * created on 01-11-2024-10h-47m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Ordering;

use App\Enums\Ordering\Purge\PurgeStateEnum;
use App\Enums\Ordering\Purge\PurgeTypeEnum;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property PurgeStateEnum $state
 * @property PurgeTypeEnum $type
 * @property int $shop_id
 * @property \Illuminate\Support\Carbon $date
 * @property string $start_purge_date
 * @property string $end_purge_date
 * @property int $inactive_days
 * @property int $estimated_orders
 * @property int $estimated_transactions
 * @property string $estimated_amount
 * @property int $number_orders
 * @property int $number_purged_orders
 * @property int $number_purged_transactions
 * @property string $purged_amount
 * @property int $number_purge_exculpated
 * @property int $number_purge_cancelled
 * @property int $number_purge_errors
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ordering\PurgedOrder> $purgedOrders
 * @property-read \App\Models\Catalogue\Shop $shop
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purge query()
 * @mixin \Eloquent
 */
class Purge extends Model
{
    use InShop;
    protected $guarded = [];

    protected $casts = [
        'type'       => PurgeTypeEnum::class,
        'state'     => PurgeStateEnum::class,
        'date'         => 'datetime',
    ];

    public function purgedOrders(): HasMany
    {
        return $this->hasMany(PurgedOrder::class);
    }

}

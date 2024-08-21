<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:26:47 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Ordering;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Ordering\OrderStats
 *
 * @property int $id
 * @property int $order_id
 * @property int $number_transactions_at_creation
 * @property int $number_add_up_transactions
 * @property int $number_cut_off_transactions
 * @property int $number_transactions transactions including cancelled
 * @property int $number_current_transactions transactions excluding cancelled
 * @property int $number_transactions_state_in_basket
 * @property int $number_transactions_state_in_process
 * @property int $number_transactions_state_in_warehouse
 * @property int $number_transactions_state_handling
 * @property int $number_transactions_state_packed
 * @property int $number_transactions_state_finalised
 * @property int $number_transactions_state_dispatched
 * @property int $number_transactions_state_cancelled
 * @property int $number_transactions_status_in_basket
 * @property int $number_transactions_status_processing
 * @property int $number_transactions_status_settled
 * @property int $number_transactions_type_order
 * @property int $number_transactions_type_refund
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Ordering\Order $order
 * @method static Builder|OrderStats newModelQuery()
 * @method static Builder|OrderStats newQuery()
 * @method static Builder|OrderStats query()
 * @mixin Eloquent
 */
class OrderStats extends Model
{
    protected $table = 'order_stats';

    protected $guarded = [];


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

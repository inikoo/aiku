<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jul 2023 12:51:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\CRM\CustomerFulfilmentStats
 *
 * @property int $id
 * @property int $customer_id
 * @property int $number_stored_items
 * @property int $number_stored_items_type_pallet
 * @property int $number_stored_items_type_box
 * @property int $number_stored_items_type_oversize
 * @property int $number_stored_items_state_in_process
 * @property int $number_stored_items_state_received
 * @property int $number_stored_items_state_booked_in
 * @property int $number_stored_items_state_settled
 * @property int $number_stored_items_status_in_process
 * @property int $number_stored_items_status_storing
 * @property int $number_stored_items_status_lost
 * @property int $number_stored_items_status_returned
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CRM\Customer $customer
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerFulfilmentStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerFulfilmentStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerFulfilmentStats query()
 * @mixin \Eloquent
 */
class CustomerFulfilmentStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'customer_fulfilment_stats';

    protected $guarded = [];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:20:42 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\CRM;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\CRM\CustomerStats
 *
 * @property int $id
 * @property int $customer_id
 * @property int $number_orders
 * @property int $number_orders_state_creating
 * @property int $number_orders_state_submitted
 * @property int $number_orders_state_handling
 * @property int $number_orders_state_packed
 * @property int $number_orders_state_finalised
 * @property int $number_orders_state_settled
 * @property int $number_orders_status_processing
 * @property int $number_orders_status_settled_dispatched
 * @property int $number_orders_status_settled_fail
 * @property int $number_orders_status_settled_cancelled
 * @property int $number_orders_handing_type_collection
 * @property int $number_orders_handing_type_shipping
 * @property int $number_invoices
 * @property int $number_invoices_type_invoice
 * @property int $number_invoices_type_refund
 * @property int $number_delivery_notes
 * @property int $number_delivery_notes_type_order
 * @property int $number_delivery_notes_type_replacement
 * @property int $number_delivery_notes_state_submitted
 * @property int $number_delivery_notes_state_in_queue
 * @property int $number_delivery_notes_state_picker_assigned
 * @property int $number_delivery_notes_state_picking
 * @property int $number_delivery_notes_state_picked
 * @property int $number_delivery_notes_state_packing
 * @property int $number_delivery_notes_state_packed
 * @property int $number_delivery_notes_state_finalised
 * @property int $number_delivery_notes_state_settled
 * @property int $number_delivery_notes_cancelled_at_state_submitted
 * @property int $number_delivery_notes_cancelled_at_state_in_queue
 * @property int $number_delivery_notes_cancelled_at_state_picker_assigned
 * @property int $number_delivery_notes_cancelled_at_state_picking
 * @property int $number_delivery_notes_cancelled_at_state_picked
 * @property int $number_delivery_notes_cancelled_at_state_packing
 * @property int $number_delivery_notes_cancelled_at_state_packed
 * @property int $number_delivery_notes_cancelled_at_state_finalised
 * @property int $number_delivery_notes_cancelled_at_state_settled
 * @property int|null $currency_id
 * @property int $number_web_users
 * @property int $number_current_web_users
 * @property int $number_clients
 * @property int $number_current_clients
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\CRM\Customer $customer
 * @method static Builder|CustomerStats newModelQuery()
 * @method static Builder|CustomerStats newQuery()
 * @method static Builder|CustomerStats query()
 * @mixin Eloquent
 */
class CustomerStats extends Model
{
    protected $guarded = [];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}

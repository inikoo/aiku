<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\CRM\CustomerStats
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $customer_id
 * @property string|null $last_submitted_order_at
 * @property string|null $last_dispatched_delivery_at
 * @property string|null $last_invoiced_at
 * @property int $number_deliveries
 * @property int $number_deliveries_type_order
 * @property int $number_deliveries_type_replacement
 * @property int $number_invoices
 * @property int $number_invoices_type_invoice
 * @property int $number_invoices_type_refund
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|CustomerStats newModelQuery()
 * @method static Builder|CustomerStats newQuery()
 * @method static Builder|CustomerStats query()
 * @method static Builder|CustomerStats whereCreatedAt($value)
 * @method static Builder|CustomerStats whereCustomerId($value)
 * @method static Builder|CustomerStats whereId($value)
 * @method static Builder|CustomerStats whereLastDispatchedDeliveryAt($value)
 * @method static Builder|CustomerStats whereLastInvoicedAt($value)
 * @method static Builder|CustomerStats whereLastSubmittedOrderAt($value)
 * @method static Builder|CustomerStats whereNumberDeliveries($value)
 * @method static Builder|CustomerStats whereNumberDeliveriesTypeOrder($value)
 * @method static Builder|CustomerStats whereNumberDeliveriesTypeReplacement($value)
 * @method static Builder|CustomerStats whereNumberInvoices($value)
 * @method static Builder|CustomerStats whereNumberInvoicesTypeInvoice($value)
 * @method static Builder|CustomerStats whereNumberInvoicesTypeRefund($value)
 * @method static Builder|CustomerStats whereOrganisationId($value)
 * @method static Builder|CustomerStats whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Models\CRM\Customer $customer
 */
class CustomerStats extends Model
{
    protected $casts = [
        'last_submitted_order_at' => 'datetime',
        'last_dispatched_delivery_at' => 'datetime',
        'last_invoiced_at' => 'datetime',
    ];
    protected $guarded = [];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

}

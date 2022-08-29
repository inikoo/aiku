<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats whereLastDispatchedDeliveryAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats whereLastInvoicedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats whereLastSubmittedOrderAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats whereNumberDeliveries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats whereNumberDeliveriesTypeOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats whereNumberDeliveriesTypeReplacement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats whereNumberInvoices($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats whereNumberInvoicesTypeInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats whereNumberInvoicesTypeRefund($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CustomerStats extends Model
{
    use HasFactory;
}

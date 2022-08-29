<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 27 Aug 2022 22:46:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Sales;

use App\Models\CRM\Customer;
use App\Models\Delivery\DeliveryNote;
use App\Models\Marketing\Shop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Sales\Order
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $shop_id
 * @property int $customer_id
 * @property int|null $customer_client_id
 * @property string|null $number
 * @property string $state
 * @property int|null $billing_address_id
 * @property int|null $delivery_address_id
 * @property int $items number of items
 * @property string $items_discounts
 * @property string $items_net
 * @property int $currency_id
 * @property string $exchange
 * @property string $charges
 * @property string|null $shipping
 * @property string $net
 * @property string $tax
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $organisation_source_id
 * @property-read Customer $customer
 * @property-read \Illuminate\Database\Eloquent\Collection|DeliveryNote[] $deliveryNotes
 * @property-read int|null $delivery_notes_count
 * @property-read Shop $shop
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order query()
 * @method static Builder|Order whereBillingAddressId($value)
 * @method static Builder|Order whereCharges($value)
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereCurrencyId($value)
 * @method static Builder|Order whereCustomerClientId($value)
 * @method static Builder|Order whereCustomerId($value)
 * @method static Builder|Order whereData($value)
 * @method static Builder|Order whereDeletedAt($value)
 * @method static Builder|Order whereDeliveryAddressId($value)
 * @method static Builder|Order whereExchange($value)
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereItems($value)
 * @method static Builder|Order whereItemsDiscounts($value)
 * @method static Builder|Order whereItemsNet($value)
 * @method static Builder|Order whereNet($value)
 * @method static Builder|Order whereNumber($value)
 * @method static Builder|Order whereOrganisationId($value)
 * @method static Builder|Order whereOrganisationSourceId($value)
 * @method static Builder|Order whereShipping($value)
 * @method static Builder|Order whereShopId($value)
 * @method static Builder|Order whereState($value)
 * @method static Builder|Order whereTax($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Order extends Model
{

    protected $casts = [
        'data' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }


    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function deliveryNotes(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryNote::class)->withTimestamps();
    }
}

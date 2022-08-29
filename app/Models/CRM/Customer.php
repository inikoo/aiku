<?php

namespace App\Models\CRM;

use App\Models\Helpers\Address;
use App\Models\Marketing\Shop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\CRM\Customer
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Customer newModelQuery()
 * @method static Builder|Customer newQuery()
 * @method static Builder|Customer query()
 * @method static Builder|Customer whereCreatedAt($value)
 * @method static Builder|Customer whereId($value)
 * @method static Builder|Customer whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property string|null $name
 * @property string|null $contact_name
 * @property string|null $company_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $identity_document_number
 * @property string|null $website
 * @property string|null $tax_number
 * @property string|null $tax_number_status
 * @property array $tax_number_data
 * @property array $location
 * @property string $status
 * @property string|null $state
 * @property string|null $trade_state number of invoices
 * @property int|null $billing_address_id
 * @property int|null $delivery_address_id null for customers in fulfilment shops
 * @property array $data
 * @property string|null $deleted_at
 * @property int|null $organisation_source_id
 * @property-read \Illuminate\Database\Eloquent\Collection|Address[] $addresses
 * @property-read int|null $addresses_count
 * @property-read Address|null $billingAddress
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CRM\CustomerClient[] $clients
 * @property-read int|null $clients_count
 * @property-read Address|null $deliveryAddress
 * @property-read Shop|null $shop
 * @property-read \App\Models\CRM\CustomerStats|null $stats
 * @method static Builder|Customer whereBillingAddressId($value)
 * @method static Builder|Customer whereCompanyName($value)
 * @method static Builder|Customer whereContactName($value)
 * @method static Builder|Customer whereData($value)
 * @method static Builder|Customer whereDeletedAt($value)
 * @method static Builder|Customer whereDeliveryAddressId($value)
 * @method static Builder|Customer whereEmail($value)
 * @method static Builder|Customer whereIdentityDocumentNumber($value)
 * @method static Builder|Customer whereLocation($value)
 * @method static Builder|Customer whereName($value)
 * @method static Builder|Customer whereOrganisationId($value)
 * @method static Builder|Customer whereOrganisationSourceId($value)
 * @method static Builder|Customer wherePhone($value)
 * @method static Builder|Customer whereShopId($value)
 * @method static Builder|Customer whereState($value)
 * @method static Builder|Customer whereStatus($value)
 * @method static Builder|Customer whereTaxNumber($value)
 * @method static Builder|Customer whereTaxNumberData($value)
 * @method static Builder|Customer whereTaxNumberStatus($value)
 * @method static Builder|Customer whereTradeState($value)
 * @method static Builder|Customer whereWebsite($value)
 */
class Customer extends Model
{
    protected $casts = [
        'data'            => 'array',
        'tax_number_data' => 'array',
        'location'        => 'array',

    ];

    protected $attributes = [
        'data'            => '{}',
        'location'        => '{}',
        'tax_number_data' => '{}',

    ];

    protected $guarded = [];

    public function addresses(): MorphToMany
    {
        return $this->morphToMany(Address::class, 'addressable')->withTimestamps();
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(CustomerClient::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(CustomerStats::class);
    }



    public function getFormattedID(): string
    {
        return sprintf('%05d', $this->id);
    }

}

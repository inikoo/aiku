<?php

namespace App\Models\CRM;

use App\Models\Helpers\Address;
use App\Models\Marketing\Shop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\CRM\CustomerClient
 *
 * @property int $id
 * @property bool $status
 * @property int|null $shop_id
 * @property int|null $customer_id
 * @property string|null $name
 * @property string|null $contact_name
 * @property string|null $company_name
 * @property string|null $email
 * @property string|null $phone
 * @property array $location
 * @property int|null $delivery_address_id
 * @property \Illuminate\Support\Carbon|null $deactivated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection|Address[] $addresses
 * @property-read int|null $addresses_count
 * @property-read \App\Models\CRM\Customer|null $customer
 * @property-read Address|null $deliveryAddress
 * @property-read Shop|null $shop
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient whereDeactivatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient whereDeliveryAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerClient whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CustomerClient extends Model
{

    use SoftDeletes;

    protected $casts = [
        'location' => 'array',
        'deactivated_at'=>'datetime'
    ];

    protected $attributes = [
        'location' => '{}',
    ];

    protected $guarded = [];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function addresses(): MorphToMany
    {
        return $this->morphToMany(Address::class, 'addressable')->withTimestamps();
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}

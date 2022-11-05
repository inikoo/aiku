<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Oct 2022 01:04:09 Greenwich Mean Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Dropshipping;

use App\Models\Helpers\Address;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Dropshipping\CustomerClient
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
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection|Address[] $addresses
 * @property-read int|null $addresses_count
 * @property-read Customer|null $customer
 * @property-read Address|null $deliveryAddress
 * @property-read Shop|null $shop
 * @method static Builder|CustomerClient newModelQuery()
 * @method static Builder|CustomerClient newQuery()
 * @method static \Illuminate\Database\Query\Builder|CustomerClient onlyTrashed()
 * @method static Builder|CustomerClient query()
 * @method static Builder|CustomerClient whereCompanyName($value)
 * @method static Builder|CustomerClient whereContactName($value)
 * @method static Builder|CustomerClient whereCreatedAt($value)
 * @method static Builder|CustomerClient whereCustomerId($value)
 * @method static Builder|CustomerClient whereDeactivatedAt($value)
 * @method static Builder|CustomerClient whereDeletedAt($value)
 * @method static Builder|CustomerClient whereDeliveryAddressId($value)
 * @method static Builder|CustomerClient whereEmail($value)
 * @method static Builder|CustomerClient whereId($value)
 * @method static Builder|CustomerClient whereLocation($value)
 * @method static Builder|CustomerClient whereName($value)
 * @method static Builder|CustomerClient wherePhone($value)
 * @method static Builder|CustomerClient whereShopId($value)
 * @method static Builder|CustomerClient whereSourceId($value)
 * @method static Builder|CustomerClient whereStatus($value)
 * @method static Builder|CustomerClient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|CustomerClient withTrashed()
 * @method static \Illuminate\Database\Query\Builder|CustomerClient withoutTrashed()
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

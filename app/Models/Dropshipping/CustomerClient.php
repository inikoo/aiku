<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Oct 2022 01:04:09 Greenwich Mean Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Dropshipping;

use App\Actions\Sales\Customer\HydrateCustomer;
use App\Models\Helpers\Address;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Traits\HasAddress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Dropshipping\CustomerClient
 *
 * @property int $id
 * @property string $slug
 * @property string|null $reference
 * @property bool $status
 * @property int|null $shop_id
 * @property int|null $customer_id
 * @property string|null $name
 * @property string|null $contact_name
 * @property string|null $company_name
 * @property string|null $email
 * @property string|null $phone
 * @property array $location
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
 * @method static Builder|CustomerClient whereReference($value)
 * @method static Builder|CustomerClient whereShopId($value)
 * @method static Builder|CustomerClient whereSlug($value)
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
    use HasSlug;
    use HasAddress;


    protected $casts = [
        'location'       => 'array',
        'deactivated_at' => 'datetime'
    ];

    protected $attributes = [
        'location' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                $slug = $this->reference;

                if ($slug=='') {
                    $slug = $this->customer->reference;
                }

                return $slug;
            })
            ->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(12);
    }

    protected static function booted()
    {
        static::creating(
            function (CustomerClient $customerClient) {
                $customerClient->name=$customerClient->company_name==''?$customerClient->contact_name:$customerClient->company_name;
            }
        );

        static::created(
            function (CustomerClient $customerClient) {
                HydrateCustomer::make()->clients($customerClient->customer);
            }
        );
        static::deleted(
            function (CustomerClient $customerClient) {
                HydrateCustomer::make()->clients($customerClient->customer);
            }
        );

        static::updated(function (CustomerClient $customerClient) {
            if ($customerClient->wasChanged('status')) {
                HydrateCustomer::make()->clients($customerClient->customer);
            }
            if ($customerClient->wasChanged(['company_name','contact_name'])) {
                $customerClient->name=$customerClient->company_name==''?$customerClient->contact_name:$customerClient->company_name;
            }
        });
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }


}

<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Oct 2022 01:04:09 Greenwich Mean Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Dropshipping;

use App\Actions\Sales\Customer\Hydrators\CustomerHydrateClients;
use App\Models\Helpers\Address;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Traits\HasAddress;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Address> $addresses
 * @property-read Customer|null $customer
 * @property-read Shop|null $shop
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static Builder|CustomerClient newModelQuery()
 * @method static Builder|CustomerClient newQuery()
 * @method static Builder|CustomerClient onlyTrashed()
 * @method static Builder|CustomerClient query()
 * @method static Builder|CustomerClient withTrashed()
 * @method static Builder|CustomerClient withoutTrashed()
 * @mixin \Eloquent
 */
class CustomerClient extends Model
{
    use SoftDeletes;
    use HasSlug;
    use HasAddress;
    use UsesTenantConnection;
    use HasUniversalSearch;

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

                if ($slug == '') {
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
                $customerClient->name = $customerClient->company_name == '' ? $customerClient->contact_name : $customerClient->company_name;
            }
        );

        static::updated(function (CustomerClient $customerClient) {
            if ($customerClient->wasChanged('status')) {
                CustomerHydrateClients::dispatch($customerClient->customer);
            }
            if ($customerClient->wasChanged(['company_name', 'contact_name'])) {
                $customerClient->name = $customerClient->company_name == '' ? $customerClient->contact_name : $customerClient->company_name;
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

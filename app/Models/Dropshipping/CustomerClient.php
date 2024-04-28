<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Oct 2022 01:04:09 Greenwich Mean Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Dropshipping;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateClients;
use App\Models\CRM\Customer;
use App\Models\Helpers\Address;
use App\Models\Market\Shop;
use App\Models\Search\UniversalSearch;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InCustomer;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Dropshipping\CustomerClient
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
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
 * @property Carbon|null $deactivated_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Collection<int, Address> $addresses
 * @property-read Customer|null $customer
 * @property-read Group $group
 * @property-read Organisation $organisation
 * @property-read Shop|null $shop
 * @property-read UniversalSearch|null $universalSearch
 * @method static \Database\Factories\Dropshipping\CustomerClientFactory factory($count = null, $state = [])
 * @method static Builder|CustomerClient newModelQuery()
 * @method static Builder|CustomerClient newQuery()
 * @method static Builder|CustomerClient onlyTrashed()
 * @method static Builder|CustomerClient query()
 * @method static Builder|CustomerClient withTrashed()
 * @method static Builder|CustomerClient withoutTrashed()
 * @mixin Eloquent
 */
class CustomerClient extends Model
{
    use SoftDeletes;
    use HasSlug;
    use HasAddresses;
    use HasUniversalSearch;
    use HasFactory;
    use InCustomer;

    protected $casts = [
        'location'       => 'array',
        'deactivated_at' => 'datetime',
        'status'         => 'boolean'
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
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(12);
    }

    protected static function booted(): void
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

}

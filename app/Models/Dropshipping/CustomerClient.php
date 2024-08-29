<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 19:08:06 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Dropshipping;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateClients;
use App\Models\Catalogue\Shop;
use App\Models\Helpers\Address;
use App\Models\Helpers\UniversalSearch;
use App\Models\Ordering\Order;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasAddress;
use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InCustomer;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Dropshipping\CustomerClient
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string|null $reference
 * @property bool $status
 * @property int|null $shop_id
 * @property int|null $customer_id
 * @property string|null $name
 * @property string|null $contact_name
 * @property string|null $company_name
 * @property string|null $email
 * @property string|null $phone
 * @property int|null $address_id
 * @property array $location
 * @property string $ulid
 * @property Carbon|null $deactivated_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Address|null $address
 * @property-read Collection<int, Address> $addresses
 * @property-read Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read \App\Models\CRM\Customer|null $customer
 * @property-read Group $group
 * @property-read Collection<int, Order> $orders
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
class CustomerClient extends Model implements Auditable
{
    use SoftDeletes;
    use HasAddress;
    use HasAddresses;
    use HasUniversalSearch;
    use HasFactory;
    use InCustomer;
    use HasHistory;

    protected $casts = [
        'location'       => 'array',
        'deactivated_at' => 'datetime',
        'status'         => 'boolean'
    ];

    protected $attributes = [
        'location' => '{}',
    ];

    protected $guarded = [];

    public function generateTags(): array
    {
        return ['crm'];
    }

    protected array $auditInclude = [
        'contact_name',
        'company_name',
        'email',
        'phone',
        'reference',
    ];

    public function getRouteKeyName(): string
    {
        return 'ulid';
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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

}

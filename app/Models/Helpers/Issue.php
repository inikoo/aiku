<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:43:17 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use App\Models\CRM\Customer;
use App\Models\Dispatch\Shipper;
use App\Models\HumanResources\Employee;
use App\Models\Inventory\Warehouse;
use App\Models\Catalogue\Shop;
use App\Models\SupplyChain\Supplier;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Helpers\Issue
 *
 * @property int $id
 * @property int $user_id
 * @property string $date
 * @property string $description
 * @property array $data
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Customer> $customer
 * @property-read Collection<int, Employee> $employee
 * @property-read Collection<int, Shipper> $shipper
 * @property-read Collection<int, Shop> $shop
 * @property-read Collection<int, Supplier> $supplier
 * @property-read Collection<int, Warehouse> $warehouse
 * @method static Builder|Issue newModelQuery()
 * @method static Builder|Issue newQuery()
 * @method static Builder|Issue onlyTrashed()
 * @method static Builder|Issue query()
 * @method static Builder|Issue withTrashed()
 * @method static Builder|Issue withoutTrashed()
 * @mixin Eloquent
 */
class Issue extends Model
{
    use SoftDeletes;

    protected $casts = [
        'data'            => 'array',
    ];

    protected $attributes = [
        'data'            => '{}',
    ];

    protected $guarded = [];

    public function supplier(): MorphToMany
    {
        return $this->morphedByMany(Supplier::class, 'issuable');
    }

    public function warehouse(): MorphToMany
    {
        return $this->morphedByMany(Warehouse::class, 'issuable');
    }

    public function shipper(): MorphToMany
    {
        return $this->morphedByMany(Shipper::class, 'issuable');
    }

    public function customer(): MorphToMany
    {
        return $this->morphedByMany(Customer::class, 'issuable');
    }

    public function shop(): MorphToMany
    {
        return $this->morphedByMany(Shop::class, 'issuable');
    }

    public function employee(): MorphToMany
    {
        return $this->morphedByMany(Employee::class, 'issuable');
    }
}

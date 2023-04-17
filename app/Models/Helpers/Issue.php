<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:43:17 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use App\Models\Dispatch\Shipper;
use App\Models\HumanResources\Employee;
use App\Models\Inventory\Warehouse;
use App\Models\Marketing\Shop;
use App\Models\Procurement\Supplier;
use App\Models\Sales\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Issue extends Model
{
    use UsesTenantConnection;
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

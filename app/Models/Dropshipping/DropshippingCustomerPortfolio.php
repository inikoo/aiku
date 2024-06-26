<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 19:08:13 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Dropshipping;

use App\Models\Catalogue\Product;
use App\Models\CRM\Customer;
use App\Models\Ordering\Platform;
use App\Models\Traits\InCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property int $customer_id
 * @property int $product_id
 * @property string|null $reference This is the reference that the customer uses to identify the product
 * @property bool $status
 * @property string|null $last_added_at
 * @property string|null $last_removed_at
 * @property array $data
 * @property array $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $source_id
 * @property-read Customer $customer
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Platform> $platforms
 * @property-read Product $product
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @property-read \App\Models\Dropshipping\DropshippingCustomerPortfolioStats|null $stats
 * @method static \Illuminate\Database\Eloquent\Builder|DropshippingCustomerPortfolio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DropshippingCustomerPortfolio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DropshippingCustomerPortfolio query()
 * @mixin \Eloquent
 */
class DropshippingCustomerPortfolio extends Model
{
    use InCustomer;

    protected $casts = [
        'data'                        => 'array',
        'settings'                    => 'array',
        'status'                      => 'boolean',
        'added_at'                    => 'datetime',
        'removed_at'                  => 'datetime',
    ];

    protected $attributes = [
        'data'           => '{}',
        'settings'       => '{}',
    ];

    protected $guarded = [];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(DropshippingCustomerPortfolioStats::class);
    }

    public function platforms(): MorphToMany
    {
        return $this->morphToMany(Platform::class, 'model', 'model_has_platforms')
            ->withPivot('group_id', 'organisation_id', 'shop_id', 'reference')
            ->withTimestamps();
    }

    public function platform(): Platform|null
    {
        /** @var Platform $platform */
        $platform = $this->platforms()->first();

        return $platform;
    }

}

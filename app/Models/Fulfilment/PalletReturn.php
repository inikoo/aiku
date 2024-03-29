<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 13 Feb 2024 16:23:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Models\CRM\Customer;
use App\Models\Inventory\Warehouse;
use App\Models\PalletReturnStats;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Fulfilment\PalletDelivery
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property string $ulid
 * @property int $fulfilment_customer_id
 * @property int $fulfilment_id
 * @property int|null $warehouse_id
 * @property string|null $customer_reference
 * @property string $reference
 * @property int $number_pallets
 * @property int $number_pallet_stored_items
 * @property int $number_stored_items
 * @property PalletReturnStateEnum $state
 * @property \Illuminate\Support\Carbon|null $in_process_at
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $picking_at
 * @property \Illuminate\Support\Carbon|null $picked_at
 * @property \Illuminate\Support\Carbon|null $dispatched_at
 * @property \Illuminate\Support\Carbon|null $cancel_at
 * @property string|null $date
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property-read Customer|null $customer
 * @property-read \App\Models\Fulfilment\Fulfilment $fulfilment
 * @property-read \App\Models\Fulfilment\FulfilmentCustomer $fulfilmentCustomer
 * @property-read Organisation $organisation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\Pallet> $pallets
 * @property-read PalletReturnStats|null $stats
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @property-read Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder|PalletReturn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletReturn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletReturn onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletReturn query()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletReturn withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletReturn withoutTrashed()
 * @mixin \Eloquent
 */

class PalletReturn extends Model
{
    use HasSlug;
    use SoftDeletes;
    use HasUniversalSearch;

    protected $guarded = [];
    protected $casts   = [
        'state'              => PalletReturnStateEnum::class,
        'in_process_at'      => 'datetime',
        'submitted_at'       => 'datetime',
        'confirmed_at'       => 'datetime',
        'picking_at'         => 'datetime',
        'picked_at'          => 'datetime',
        'dispatched_at'      => 'datetime',
        'cancel_at'          => 'datetime',
        'data'               => 'array'
    ];

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('reference')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(64);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function fulfilment(): BelongsTo
    {
        return $this->belongsTo(Fulfilment::class);
    }

    public function fulfilmentCustomer(): BelongsTo
    {
        return $this->belongsTo(FulfilmentCustomer::class);
    }

    public function pallets(): HasMany
    {
        return $this->hasMany(Pallet::class, 'pallet_return_id');
    }

    public function stats(): HasOne
    {
        return $this->hasOne(PalletReturnStats::class);
    }
}

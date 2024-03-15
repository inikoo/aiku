<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 13 Mar 2024 09:35:20 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\Fulfilment\StoredItemReturn\StoredItemReturnStateEnum;
use App\Models\CRM\Customer;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int $id
 * * @property int $group_id
 * * @property int $organisation_id
 * * @property string $slug
 * * @property string $ulid
 * * @property int $fulfilment_customer_id
 * * @property int $fulfilment_id
 * * @property int|null $warehouse_id
 * * @property string|null $customer_reference
 * * @property string $reference
 * * @property int $number_pallets
 * * @property int $number_pallet_stored_items
 * * @property int $number_stored_items
 * * @property PalletReturnStateEnum $state
 * * @property \Illuminate\Support\Carbon|null $in_process_at
 * * @property \Illuminate\Support\Carbon|null $submitted_at
 * * @property \Illuminate\Support\Carbon|null $confirmed_at
 * * @property \Illuminate\Support\Carbon|null $in_delivery_at
 * * @property \Illuminate\Support\Carbon|null $received_at
 * * @property \Illuminate\Support\Carbon|null $done_at
 * * @property string|null $dispatched_at
 * * @property string|null $date
 * * @property array|null $data
 * * @property \Illuminate\Support\Carbon|null $created_at
 * * @property \Illuminate\Support\Carbon|null $updated_at
 * * @property \Illuminate\Support\Carbon|null $deleted_at
 * * @property string|null $delete_comment
 * * @property-read Customer|null $customer
 * * @property-read \App\Models\Fulfilment\Fulfilment $fulfilment
 * * @property-read \App\Models\Fulfilment\FulfilmentCustomer $fulfilmentCustomer
 * * @property-read Organisation $organisation
 * * @property-read \App\Models\Fulfilment\StoredItem $items
 * * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\Pallet> $pallets
 * * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * * @property-read Warehouse|null $warehouse
 */

class StoredItemReturn extends Model
{
    use HasSlug;

    protected $guarded = [];
    protected $casts   = [
        'state'              => StoredItemReturnStateEnum::class,
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
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('reference')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(64);
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

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(StoredItem::class, 'stored_item_return_stored_items')->withPivot('quantity');
    }
}

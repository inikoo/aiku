<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 15:20:49 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Actions\Utils\Abbreviate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\MovementPallet;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Fulfilment\Pallet
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property string|null $customer_reference
 * @property int $fulfilment_id
 * @property int $fulfilment_customer_id
 * @property int $warehouse_id
 * @property int|null $location_id
 * @property PalletStatusEnum $status
 * @property PalletStateEnum $state
 * @property PalletTypeEnum $type
 * @property string $notes
 * @property string $items_quantity
 * @property string|null $received_at
 * @property string|null $booked_in_at
 * @property string|null $settled_at
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $source_id
 * @property-read \App\Models\Fulfilment\Fulfilment $fulfilment
 * @property-read \App\Models\Fulfilment\FulfilmentCustomer $fulfilmentCustomer
 * @property-read Location|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MovementPallet> $movements
 * @property-read Organisation $organisation
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @property-read Warehouse $warehouse
 * @method static \Database\Factories\Fulfilment\PalletFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Pallet located($located)
 * @method static \Illuminate\Database\Eloquent\Builder|Pallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pallet onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Pallet query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pallet withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Pallet withoutTrashed()
 * @mixin \Eloquent
 */
class Pallet extends Model
{
    use HasSlug;
    use SoftDeletes;
    use HasFactory;
    use HasUniversalSearch;


    protected $guarded = [];
    protected $casts   = [
        'data'   => 'array',
        'state'  => PalletStateEnum::class,
        'status' => PalletStatusEnum::class,
        'type'   => PalletTypeEnum::class
    ];

    protected $attributes = [
        'data'  => '{}',
        'notes' => '',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }


    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                $slug =$this->fulfilmentCustomer->slug;

                if ($this->customer_reference != '') {
                    $slug .=' '.$this->customer_reference;
                } elseif($this->notes) {
                    $slug .=' '.Abbreviate::run($this->notes);
                }
                return $slug;
            })
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(12);
    }

    public function scopeLocationId(Builder $query, $located): Builder
    {
        if ($located) {
            return $query->whereNotNull('location_id');
        }

        return $query->whereNull('location_id');
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function fulfilment(): BelongsTo
    {
        return $this->belongsTo(Fulfilment::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function fulfilmentCustomer(): BelongsTo
    {
        return $this->belongsTo(FulfilmentCustomer::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(MovementPallet::class);
    }
}

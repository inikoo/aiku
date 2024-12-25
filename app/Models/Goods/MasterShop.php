<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 25 Dec 2024 02:15:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Goods;

use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\ShopStats;
use App\Models\SysAdmin\Group;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasImage;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Collection as LaravelCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property string $slug
 * @property string $code
 * @property string|null $name
 * @property bool $status
 * @property ShopTypeEnum $type
 * @property int|null $image_id
 * @property array $data
 * @property array $settings
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property ShopStateEnum $state
 * @property-read LaravelCollection<int, \App\Models\Helpers\Audit> $audits
 * @property-read Group $group
 * @property-read \App\Models\Helpers\Media|null $image
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Helpers\Media> $images
 * @property-read LaravelCollection<int, \App\Models\Goods\MasterProductCategory> $masterProductCategories
 * @property-read LaravelCollection<int, \App\Models\Goods\MasterProduct> $masterProducts
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Helpers\Media> $media
 * @property-read \App\Models\Goods\MasterShopSalesIntervals|null $salesIntervals
 * @property-read ShopStats|null $stats
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterShop newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterShop newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterShop onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterShop query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterShop withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterShop withoutTrashed()
 * @mixin \Eloquent
 */
class MasterShop extends Model implements HasMedia, Auditable
{
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;
    use HasHistory;
    use HasImage;

    protected $casts = [
        'data'            => 'array',
        'settings'        => 'array',
        'type'            => ShopTypeEnum::class,
        'state'           => ShopStateEnum::class,
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function generateTags(): array
    {
        return [
            'catalogue'
        ];
    }

    protected array $auditInclude = [
        'code',
        'name',
        'state',
    ];


    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(6);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(ShopStats::class);
    }

    public function masterProductCategories(): HasMany
    {
        return $this->hasMany(MasterProductCategory::class);
    }

    public function masterDepartments(): LaravelCollection
    {
        return $this->masterProductCategories()->where('type', ProductCategoryTypeEnum::DEPARTMENT)->get();
    }

    public function masterSubDepartments(): LaravelCollection
    {
        return $this->masterProductCategories()->where('type', ProductCategoryTypeEnum::SUB_DEPARTMENT)->get();
    }

    public function masterFamilies(): LaravelCollection
    {
        return $this->masterProductCategories()->where('type', ProductCategoryTypeEnum::FAMILY)->get();
    }

    public function masterProducts(): BelongsToMany
    {
        return $this->belongsToMany(MasterProduct::class, 'master_shop_has_master_products')
            ->withTimestamps();
    }

    public function salesIntervals(): HasOne
    {
        return $this->hasOne(MasterShopSalesIntervals::class);
    }


}

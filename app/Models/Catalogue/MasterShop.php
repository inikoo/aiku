<?php
/*
 * author Arya Permana - Kirin
 * created on 15-10-2024-09h-14m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Catalogue;

use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
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


}

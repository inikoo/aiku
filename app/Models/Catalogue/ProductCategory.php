<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 18:49:22 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use App\Enums\Catalogue\ProductCategory\ProductCategoryStateEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Helpers\UniversalSearch;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasImage;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InShop;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as LaravelCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Catalogue\Department
 *
 * @property int $id
 * @property ProductCategoryTypeEnum $type
 * @property ProductCategoryStateEnum $state
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property int|null $department_id
 * @property int|null $sub_department_id
 * @property int|null $parent_id
 * @property string $slug
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property int|null $image_id
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $source_department_id
 * @property string|null $source_family_id
 * @property-read LaravelCollection<int, \App\Models\Helpers\Audit> $audits
 * @property-read LaravelCollection<int, ProductCategory> $children
 * @property-read ProductCategory|null $department
 * @property-read Group $group
 * @property-read \App\Models\Helpers\Media|null $image
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Helpers\Media> $images
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Helpers\Media> $media
 * @property-read Organisation $organisation
 * @property-read ProductCategory|null $parent
 * @property-read \App\Models\Catalogue\ProductCategorySalesIntervals|null $salesIntervals
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @property-read \App\Models\Catalogue\ProductCategoryStats|null $stats
 * @property-read ProductCategory|null $subDepartment
 * @property-read LaravelCollection<int, ProductCategory> $subDepartments
 * @property-read UniversalSearch|null $universalSearch
 * @method static \Database\Factories\Catalogue\ProductCategoryFactory factory($count = null, $state = [])
 * @method static Builder|ProductCategory newModelQuery()
 * @method static Builder|ProductCategory newQuery()
 * @method static Builder|ProductCategory onlyTrashed()
 * @method static Builder|ProductCategory query()
 * @method static Builder|ProductCategory withTrashed()
 * @method static Builder|ProductCategory withoutTrashed()
 * @mixin Eloquent
 */
class ProductCategory extends Model implements Auditable, HasMedia
{
    use HasSlug;
    use SoftDeletes;
    use HasUniversalSearch;
    use HasFactory;
    use HasHistory;
    use InShop;
    use HasImage;

    protected $guarded = [];

    protected $casts = [
        'data'  => 'array',
        'state' => ProductCategoryStateEnum::class,
        'type'  => ProductCategoryTypeEnum::class,
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function generateTags(): array
    {
        return [
            'catalogue',
        ];
    }

    protected array $auditInclude = [
        'code',
        'name',
        'description',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return $this->code.'-'.$this->shop->code;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(64);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(ProductCategoryStats::class);
    }

    public function salesIntervals(): HasOne
    {
        return $this->hasOne(ProductCategorySalesIntervals::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'department_id');
    }

    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'sub_department_id');
    }

    public function subDepartments(): HasMany
    {
        if($this->type==ProductCategoryTypeEnum::DEPARTMENT) {
            return $this->hasMany(ProductCategory::class, 'department_id');
        }

        return $this->hasMany(ProductCategory::class, 'sub_department_id');
    }


    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    public function families(): LaravelCollection
    {
        return $this->children()->where('type', ProductCategoryTypeEnum::FAMILY)->get();
    }


    public function products(): HasMany
    {
        return match ($this->type) {
            ProductCategoryTypeEnum::DEPARTMENT => $this->hasMany(Product::class, 'department_id'),
            ProductCategoryTypeEnum::FAMILY     => $this->hasMany(Product::class, 'family_id'),
            default                             => null
        };

    }
}

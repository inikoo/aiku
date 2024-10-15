<?php
/*
 * author Arya Permana - Kirin
 * created on 14-10-2024-13h-19m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Catalogue;

use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Models\SysAdmin\Group;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasImage;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property int|null $master_family_id
 * @property int|null $master_sub_department_id
 * @property int|null $master_department_id
 * @property bool $is_main
 * @property bool $status
 * @property ProductStateEnum $state
 * @property string $slug
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property string $unit
 * @property string|null $price
 * @property array $data
 * @property array $settings
 * @property int|null $gross_weight outer weight including packing, grams
 * @property int|null $marketing_weight to be shown in website, grams
 * @property string|null $barcode mirror from trade_unit
 * @property string|null $rrp RRP per outer
 * @property int|null $image_id
 * @property int|null $available_quantity outer available quantity for sale
 * @property string $variant_ratio
 * @property bool $variant_is_visible
 * @property int|null $main_master_product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read Group $group
 * @property-read \App\Models\Helpers\Media|null $image
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Helpers\Media> $images
 * @property-read MasterProduct|null $mainMasterProduct
 * @property-read \App\Models\Catalogue\MasterProductCategory|null $masterDepartment
 * @property-read \App\Models\Catalogue\MasterProductCategory|null $masterFamily
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MasterProduct> $masterProductVariants
 * @property-read \App\Models\Catalogue\MasterProductCategory|null $masterSubDepartment
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Helpers\Media> $media
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|MasterProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MasterProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MasterProduct onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MasterProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|MasterProduct withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MasterProduct withoutTrashed()
 * @mixin \Eloquent
 */
class MasterProduct extends Model implements Auditable, HasMedia
{
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;
    use HasHistory;
    use HasImage;

    protected $guarded = [];

    protected $casts = [
        'variant_ratio'          => 'decimal:3',
        'price'                  => 'decimal:2',
        'rrp'                    => 'decimal:2',
        'data'                   => 'array',
        'settings'               => 'array',
        'status'                 => 'boolean',
        'variant_is_visible'     => 'boolean',
        'state'                  => ProductStateEnum::class,
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
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
        'status',
        'state',
        'price',
        'rrp',
        'unit',
        'is_auto_assign',
        'auto_assign_trigger',
        'auto_assign_subject',
        'auto_assign_subject_type',
        'auto_assign_status',
        'is_main',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return $this->code.'-'.$this->group->code;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(64);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function masterProductVariants(): HasMany
    {
        return $this->hasMany(MasterProduct::class, 'main_master_product_id');
    }

    public function mainMasterProduct(): BelongsTo
    {
        return $this->belongsTo(MasterProduct::class, 'main_master_product_id');
    }

    public function masterDepartment(): BelongsTo
    {
        return $this->belongsTo(MasterProductCategory::class, 'master_department_id');
    }

    public function masterSubDepartment(): BelongsTo
    {
        return $this->belongsTo(MasterProductCategory::class, 'master_sub_department_id');
    }

    public function masterFamily(): BelongsTo
    {
        return $this->belongsTo(MasterProductCategory::class, 'master_family_id');
    }

}

<?php
/*
 * author Arya Permana - Kirin
 * created on 14-10-2024-13h-19m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Catalogue;

use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Catalogue\Product\ProductUnitRelationshipType;
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

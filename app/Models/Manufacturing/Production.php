<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 12:17:53 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Manufacturing;

use App\Enums\Inventory\Warehouse\WarehouseStateEnum;
use App\Models\SysAdmin\Role;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InOrganisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property WarehouseStateEnum $state
 * @property array $settings
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Manufacturing\Artefact> $artefacts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Manufacturing\JobOrder> $jobOrders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Manufacturing\ManufactureTask> $manufactureTasks
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Manufacturing\RawMaterial> $rawMaterials
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Role> $roles
 * @property-read \App\Models\Manufacturing\ProductionStats|null $stats
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|Production newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Production newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Production onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Production query()
 * @method static \Illuminate\Database\Eloquent\Builder|Production withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Production withoutTrashed()
 * @mixin \Eloquent
 */
class Production extends Model implements Auditable
{
    use HasSlug;
    use SoftDeletes;
    use HasUniversalSearch;
    use HasHistory;
    use InOrganisation;

    protected $casts = [
        'state'    => WarehouseStateEnum::class,
        'data'     => 'array',
        'settings' => 'array'
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

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(4);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(ProductionStats::class);
    }

    public function roles(): MorphMany
    {
        return $this->morphMany(Role::class, 'scope');
    }

    public function rawMaterials(): HasMany
    {
        return $this->hasMany(RawMaterial::class);
    }

    public function manufactureTasks(): HasMany
    {
        return $this->hasMany(ManufactureTask::class);
    }

    public function artefacts(): HasMany
    {
        return $this->hasMany(Artefact::class);
    }

    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }

}

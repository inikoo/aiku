<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 14:36:10 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use App\Enums\Web\WebBlockTypeCategory\WebBlockTypeCategorySlugEnum;
use App\Enums\Web\WebBlockTypeCategory\WebBlockTypeCategoryScopeEnum;
use App\Models\Traits\InGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property WebBlockTypeCategoryScopeEnum $scope
 * @property WebBlockTypeCategorySlugEnum $slug
 * @property string $name
 * @property array $blueprint
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\Web\WebBlockTypeCategoryStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Web\WebBlockType> $webBlockTypes
 * @method static Builder|WebBlockTypeCategory newModelQuery()
 * @method static Builder|WebBlockTypeCategory newQuery()
 * @method static Builder|WebBlockTypeCategory onlyTrashed()
 * @method static Builder|WebBlockTypeCategory query()
 * @method static Builder|WebBlockTypeCategory withTrashed()
 * @method static Builder|WebBlockTypeCategory withoutTrashed()
 * @mixin \Eloquent
 */
class WebBlockTypeCategory extends Model
{
    use SoftDeletes;
    use InGroup;

    protected $casts = [
        'blueprint' => 'array',
        'data'      => 'array',
        'slug'      => WebBlockTypeCategorySlugEnum::class,
        'scope'     => WebBlockTypeCategoryScopeEnum::class,
    ];

    protected $attributes = [
        'blueprint' => '{}',
        'data'      => '{}',
    ];

    protected $guarded = [];

    public function webBlockTypes(): HasMany
    {
        return $this->hasMany(WebBlockType::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(WebBlockTypeCategoryStats::class);
    }

}

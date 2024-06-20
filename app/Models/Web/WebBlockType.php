<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 15:29:01 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use App\Enums\Web\WebBlockType\WebBlockTypeScopeEnum;
use App\Models\Traits\InGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $web_block_type_category_id
 * @property string $slug
 * @property WebBlockTypeScopeEnum $scope
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property array $blueprint
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\Web\WebBlockTypeStats|null $stats
 * @property-read \App\Models\Web\WebBlockTypeCategory $webBlockTypeCategory
 * @method static Builder|WebBlockType newModelQuery()
 * @method static Builder|WebBlockType newQuery()
 * @method static Builder|WebBlockType query()
 * @mixin \Eloquent
 */
class WebBlockType extends Model
{
    use HasSlug;
    use InGroup;

    protected $casts = [
        'blueprint' => 'array',
        'data'      => 'array',
        'scope'     => WebBlockTypeScopeEnum::class,
    ];

    protected $attributes = [
        'blueprint' => '{}',
        'data'      => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function webBlockTypeCategory(): BelongsTo
    {
        return $this->belongsTo(WebBlockTypeCategory::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(WebBlockTypeStats::class);
    }


}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 15:29:01 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use App\Enums\Web\WebBlock\WebBlockScopeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Web\WebBlock
 *
 * @property int $id
 * @property string $slug
 * @property WebBlockScopeEnum $scope
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property int $web_block_type_id
 * @property array $blueprint
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Web\WebBlockStats|null $stats
 * @property-read \App\Models\Web\WebBlockType $webBlockType
 * @method static Builder|WebBlock newModelQuery()
 * @method static Builder|WebBlock newQuery()
 * @method static Builder|WebBlock onlyTrashed()
 * @method static Builder|WebBlock query()
 * @method static Builder|WebBlock withTrashed()
 * @method static Builder|WebBlock withoutTrashed()
 * @mixin \Eloquent
 */
class WebBlock extends Model
{
    use SoftDeletes;
    use HasSlug;

    protected $casts = [
        'blueprint' => 'array',
        'data'      => 'array',
        'scope'     => WebBlockScopeEnum::class,
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

    public function webBlockType(): BelongsTo
    {
        return $this->belongsTo(WebBlockType::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(WebBlockStats::class);
    }


}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 14:36:10 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use App\Enums\Web\WebBlockType\WebBlockTypeClassEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Web\WebBlockType
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property WebBlockTypeClassEnum $class
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Web\WebBlockTypeStats|null $stats
 * @method static \Illuminate\Database\Eloquent\Builder|WebBlockType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebBlockType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebBlockType onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WebBlockType query()
 * @method static \Illuminate\Database\Eloquent\Builder|WebBlockType withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WebBlockType withoutTrashed()
 * @mixin \Eloquent
 */
class WebBlockType extends Model
{
    use UsesLandlordConnection;
    use SoftDeletes;
    use HasSlug;

    protected $casts = [
        'data'  => 'array',
        'class' => WebBlockTypeClassEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];


    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function stats(): HasOne
    {
        return $this->hasOne(WebBlockTypeStats::class);
    }

}

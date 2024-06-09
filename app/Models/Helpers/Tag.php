<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 15 Nov 2023 12:59:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use App\Models\Traits\HasTagSlug;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Spatie\Tags\Tag as BaseTag;

/**
 * App\Models\Helpers\Tag
 *
 * @property int $id
 * @property array $name
 * @property array $slug
 * @property string|null $tag_slug
 * @property string|null $label
 * @property string|null $type
 * @property int|null $order_column
 * @property int $number_subjects
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Helpers\TagCrmStats|null $crmStats
 * @property-read mixed $translations
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @method static Builder|Tag containing(string $name, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newQuery()
 * @method static Builder|Tag ordered(string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Tag query()
 * @method static Builder|Tag whereJsonContainsLocale(string $column, string $locale, ?mixed $value)
 * @method static Builder|Tag whereJsonContainsLocales(string $column, array $locales, ?mixed $value)
 * @method static Builder|Tag whereLocale(string $column, string $locale)
 * @method static Builder|Tag whereLocales(string $column, array $locales)
 * @method static Builder|Tag withType(?string $type = null)
 * @mixin \Eloquent
 */
class Tag extends BaseTag
{
    use HasTagSlug;
    use HasUniversalSearch;


    public function getRouteKeyName(): string
    {
        return 'tag_slug';
    }

    public function crmStats(): HasOne
    {
        return $this->hasOne(TagCrmStats::class);
    }
}

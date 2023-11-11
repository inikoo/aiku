<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Nov 2023 00:40:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Search;

use App\Models\Organisation\Organisation;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

/**
 * App\Models\Search\UniversalSearch
 *
 * @property int $id
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string|null $section
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $model
 * @property-read Organisation $organisation
 * @method static Builder|UniversalSearch newModelQuery()
 * @method static Builder|UniversalSearch newQuery()
 * @method static Builder|UniversalSearch query()
 * @mixin Eloquent
 */
class UniversalSearch extends Model
{
    use Searchable;

    protected $casts = [
        'organisations'            => 'array',


    ];

    protected $attributes = [
        'organisations'            => '{}',
    ];

    protected $guarded = [];



    public function searchableAs(): string
    {
        return config('app.universal_search_index');
    }

    public function toSearchableArray(): array
    {
        return Arr::except($this->toArray(), ['updated_at', 'created_at']);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }


}

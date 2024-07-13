<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:35:24 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

/**
 * App\Models\Search\UniversalSearch
 *
 * @property int $id
 * @property string|null $slug
 * @property int|null $group_id
 * @property int|null $organisation_id
 * @property string|null $organisation_slug
 * @property int|null $shop_id
 * @property string|null $shop_slug
 * @property int|null $fulfilment_id
 * @property string|null $fulfilment_slug
 * @property int|null $warehouse_id
 * @property string|null $warehouse_slug
 * @property int|null $website_id
 * @property string|null $website_slug
 * @property int|null $customer_id
 * @property string|null $customer_slug
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string|null $section
 * @property string $title
 * @property string|null $description
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $model
 * @method static Builder|UniversalSearch newModelQuery()
 * @method static Builder|UniversalSearch newQuery()
 * @method static Builder|UniversalSearch query()
 * @mixin \Eloquent
 */
class UniversalSearch extends Model
{
    use Searchable;

    protected $casts = [
        'data'                        => 'array',
    ];

    protected $attributes = [
        'data'           => '{}',
    ];

    protected $guarded = [];

    protected $table = 'universal_searches';

    public function searchableAs(): string
    {
        return config('elasticsearch.index_prefix').'search';
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

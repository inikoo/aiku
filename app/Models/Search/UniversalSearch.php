<?php

namespace App\Models\Search;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Laravel\Scout\Searchable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Search\UniversalSearch
 *
 * @property int $id
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string $primary_term
 * @property string|null $secondary_term
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|UniversalSearch newModelQuery()
 * @method static Builder|UniversalSearch newQuery()
 * @method static Builder|UniversalSearch query()
 * @mixin Eloquent
 */
class UniversalSearch extends Model
{
    use UsesTenantConnection;
    use Searchable;

    protected $guarded = [];

    public function searchableAs(): string
    {
        $index = array_filter([config('app.name'), App::environment('production') ? null : App::environment(), app('currentTenant')->slug, 'universal_search']);
        return implode('_', $index);
    }

    public function toSearchableArray(): array
    {
        return Arr::except($this->toArray(), ['updated_at', 'created_at']);
    }
}

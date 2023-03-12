<?php

namespace App\Models\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|UniversalSearch newModelQuery()
 * @method static Builder|UniversalSearch newQuery()
 * @method static Builder|UniversalSearch query()
 * @mixin \Eloquent
 */
class UniversalSearch extends Model
{
    use UsesTenantConnection;
    use Searchable;

    protected $guarded = [];
}

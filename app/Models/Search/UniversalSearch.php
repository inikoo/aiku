<?php

namespace App\Models\Search;

use App\Models\Tenancy\Tenant;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Search\UniversalSearch
 *
 * @property int $id
 * @property int $tenant_id
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string|null $section
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $model
 * @property-read Tenant $tenant
 * @method static Builder|UniversalSearch newModelQuery()
 * @method static Builder|UniversalSearch newQuery()
 * @method static Builder|UniversalSearch query()
 * @mixin Eloquent
 */
class UniversalSearch extends Model
{
    use Searchable;
    use UsesTenantConnection;

    protected $guarded = [];


    public static function boot(): void
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->tenant_id ??= Tenant::current()?->id;
        });
    }


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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}

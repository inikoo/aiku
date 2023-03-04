<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Marketing\HistoricRent
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|HistoricRent newModelQuery()
 * @method static Builder|HistoricRent newQuery()
 * @method static Builder|HistoricRent query()
 * @mixin \Eloquent
 */
class HistoricRent extends Model
{
    use UsesTenantConnection;
}

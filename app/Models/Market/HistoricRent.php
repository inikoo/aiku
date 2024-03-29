<?php

namespace App\Models\Market;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Market\HistoricRent
 *
 * @property int $id
 * @property int $rent_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|HistoricRent newModelQuery()
 * @method static Builder|HistoricRent newQuery()
 * @method static Builder|HistoricRent query()
 * @mixin Eloquent
 */
class HistoricRent extends Model
{
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MovementPallet
 *
 * @property int $id
 * @property int $pallet_id
 * @property int|null $location_from_id
 * @property int|null $location_to_id
 * @property \Illuminate\Support\Carbon $moved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MovementPallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MovementPallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MovementPallet query()
 * @mixin \Eloquent
 */
class MovementPallet extends Model
{
    protected $guarded = [];
    protected $casts   = [
            'moved_at'   => 'datetime'
        ];
}

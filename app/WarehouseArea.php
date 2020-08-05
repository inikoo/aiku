<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\WarehouseArea
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Location[] $locations
 * @property-read int|null $locations_count
 * @property-read \App\Warehouse $warehouse
 * @method static Builder|WarehouseArea newModelQuery()
 * @method static Builder|WarehouseArea newQuery()
 * @method static Builder|WarehouseArea query()
 * @mixin \Eloquent
 */
class WarehouseArea extends Model
{

    use UsesTenantConnection;

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    public function warehouse()
    {
        return $this->belongsTo('App\Warehouse');
    }

    public function locations()
    {
        return $this->hasMany('App\Location');
    }

}

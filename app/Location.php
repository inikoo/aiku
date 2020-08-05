<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Location
 *
 * @property-read \App\WarehouseArea $area
 * @method static Builder|Location newModelQuery()
 * @method static Builder|Location newQuery()
 * @method static Builder|Location query()
 * @mixin \Eloquent
 */
class Location extends Model
{
    use UsesTenantConnection;

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];



    public function area()
    {
        return $this->belongsTo('App\WarehouseArea');
    }



}

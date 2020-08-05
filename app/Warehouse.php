<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Warehouse
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WarehouseArea[] $areas
 * @property-read int|null $areas_count
 * @method static Builder|Warehouse newModelQuery()
 * @method static Builder|Warehouse newQuery()
 * @method static Builder|Warehouse query()
 * @mixin \Eloquent
 */
class Warehouse extends Model
{

    use UsesTenantConnection;

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    public function areas()
    {
        return $this->hasMany('App\WarehouseArea');
    }



}

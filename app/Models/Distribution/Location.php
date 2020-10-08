<?php

namespace App\Models\Distribution;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Models\Distribution\Location
 *
 * @property int $id
 * @property string $created_at
 * @property string $deleted_at
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Location extends Model implements Auditable{
    use UsesTenantConnection, Sluggable;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

        protected $casts = [
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function sluggable() {
        return [
            'slug' => [
                'source' => 'code'
            ]
        ];
    }

    protected $guarded = [];

    public function warehouse() {
        return $this->belongsTo('App\Models\Distribution\Warehouse');
    }

    public function warehouse_area() {
        return $this->belongsTo('App\Models\Distribution\WarehouseArea');
    }

    public function stocks() {
        return $this->belongsToMany('App\Models\Distribution\Stock')->withTimestamps()->withPivot('quantity');
    }


}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


class Area extends Model
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

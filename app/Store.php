<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Store extends Model
{
    use UsesTenantConnection;

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    
    public function website()
    {
        return $this->hasOne('App\Website');
    }

    
    public function customers()
    {
        return $this->hasMany('App\Customer');
    }
    

    

}

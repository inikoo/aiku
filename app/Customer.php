<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


class Customer extends Model
{
       
    use UsesTenantConnection;

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

     
    public function orders()
    {
        return $this->hasMany('App\Order');
    }

    public function deliverynotes()
    {
        return $this->hasMany('App\Deliverynote');
    }
    
}

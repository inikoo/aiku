<?php
<<<<<<< Updated upstream
=======
/*
Copyright (c) 2020, AIku.io

Version 4
*/
>>>>>>> Stashed changes

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

<<<<<<< Updated upstream
class Store extends Model
{
=======
class Store extends Model {
>>>>>>> Stashed changes
    use UsesTenantConnection;

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

<<<<<<< Updated upstream
    
    public function website()
    {
        return $this->hasOne('App\Website');
    }

    
    public function customers()
    {
        return $this->hasMany('App\Customer');
    }
    

    

=======
    public function prospects()
    {
        return $this->hasMany('App\Prospect');
    }
>>>>>>> Stashed changes
}

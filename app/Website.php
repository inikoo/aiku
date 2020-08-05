<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


class Website extends Model
{
    
    use UsesTenantConnection;

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function products()
    {
        return $this->hasMany('App\Product');
    }

    public function customers()
    {
        return $this->hasMany('App\Customer');
    }

    public function webpages()
    {
        return $this->hasMany('App\Webpage');
    }

}

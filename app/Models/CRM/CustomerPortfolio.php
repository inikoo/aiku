<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 22 Oct 2020 19:45:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 *
 * @property int $id
 * @property string $created_at
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class CustomerPortfolio extends Model{
    use UsesTenantConnection;
    use SoftDeletes;

    protected $table = 'customer_portfolio';


    protected $casts = [
        'data'     => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];


    protected $guarded = [];

    public function customer() {
        return $this->belongsTo('App\Models\CRM\Customer')->withTrashed();
    }

    public function product() {
        return $this->belongsTo('App\Models\Stores\Product');
    }




}

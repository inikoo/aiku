<?php
/*
Author: Raul Perusquía (raul@inikoo.com)
Created:  Mon Jul 27 2020 17:46:42 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/


namespace App;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

use Spatie\Multitenancy\Models\Tenant as Tenanto;


/**
 * App\Tenant
 *
 * @property int $id
 * @property string $name
 * @property string $subdomain
 * @property string $database
 * @property array $settings
 * @property array $data
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Tenant extends Tenanto {
    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $guarded = [];

    protected $attributes = [
        'data' => '{}',
        'settings' => '{}'
    ];

    protected static function booted() {
        static::created(
            function ($tenant) {

                if (ctype_alpha($tenant->database) or ctype_lower($tenant->database)) {
                    throw new Exception('Invalid database name');
                }

                DB::connection('scaffolding')->statement("DROP DATABASE IF EXISTS " . $tenant->database);
                DB::connection('scaffolding')->statement("CREATE DATABASE ".$tenant->database." ENCODING 'UTF8' LC_COLLATE = 'en_GB.UTF-8' LC_CTYPE = 'en_GB.UTF-8' TEMPLATE template0");
                DB::connection('scaffolding')->statement("CREATE EXTENSION IF NOT EXISTS timescaledb CASCADE;");

                Artisan::call('tenants:artisan "migrate:refresh --database=tenant" --tenant='.$tenant->id );
                Artisan::call('tenants:artisan "db:seed --database=tenant --class=NewTenantSeeder" --tenant='.$tenant->id);
                Artisan::call('tenants:artisan "db:seed --database=tenant --class=CountrySeeder" --tenant='.$tenant->id);
                Artisan::call('tenants:artisan "db:seed --database=tenant --class=TaxBandSeeder" --tenant='.$tenant->id);


            }
        );
    }



    public function agents() {
        return $this->belongsToMany('App\Models\Suppliers\Agent')->withTimestamps();
    }

    public function supplier_owner() {
        return $this->morphOne('App\Models\Suppliers\Supplier', 'owner');
    }


}

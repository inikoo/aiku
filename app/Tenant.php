<?php
/*
Author: Raul Perusquía (raul@inikoo.com)
Created:  Mon Jul 27 2020 17:46:42 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/


namespace App;

use Exception;
use Artisan;
use DB;

use Spatie\Multitenancy\Models\Tenant as Tenanto;

/**
 * Class Tenant
 *
 * @package App
 * @property int $id
 * @property string $name
 * @property string $subdomain
 * @property string $database
 * @property array $settings
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Agent[] $agents
 * @property-read int|null $agents_count
 * @property-read \App\Supplier|null $supplier_owner
 * @mixin \Eloquent
 * @method static \Spatie\Multitenancy\TenantCollection|static[] all($columns = ['*'])
 * @method static \Spatie\Multitenancy\TenantCollection|static[] get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tenant query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tenant whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tenant whereDatabase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tenant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tenant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tenant whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tenant whereSubdomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tenant whereUpdatedAt($value)
 */
class Tenant extends Tenanto {
    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];


    public static function booted() {
        static::created(fn(Tenant $model) => $model->post_creation_actions());
    }

    /**
     * @throws \Exception
     */
    public function post_creation_actions() {

        if (ctype_alpha($this->database) or ctype_lower($this->database)) {
            throw new Exception('Invalid database name');
        }

        DB::connection('scaffolding')->statement("DROP DATABASE IF EXISTS " . $this->database);
        DB::connection('scaffolding')->statement("CREATE DATABASE IF NOT EXISTS " . $this->database);
        Artisan::call('tenants:artisan "migrate --database=tenant" --tenant='.$this->id );

    }

    public function agents() {
        return $this->belongsToMany('App\Agent')->withTimestamps();
    }

    public function supplier_owner() {
        return $this->morphOne('App\Supplier', 'owner');
    }
}

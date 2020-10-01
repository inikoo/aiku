<?php
/*
Author: Raul Perusquia (raul@inikoo.com)
Created:  Mon Jul 27 2020 19:53:46 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/


namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Cviebrock\EloquentSluggable\Sluggable;


/**
 * App\Models\HR\Employee
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\HR\Timesheet[] $timesheets
 * @property-read int|null                                                            $timesheets_count
 * @property-read \App\User|null                                                      $user


 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Employee extends Model implements Auditable {
    use UsesTenantConnection, Sluggable;
    use \OwenIt\Auditing\Auditable;


    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $guarded = [];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}'
    ];

    public function sluggable() {
        return [
            'slug' => [
                'source'   => 'name',
                'onUpdate' => true
            ]
        ];
    }

    public function user() {
        return $this->morphOne('App\User', 'userable');
    }

    public function timesheets() {
        return $this->hasMany('App\Models\HR\Timesheet');
    }

    public function job_position() {
        return $this->belongsTo('App\Models\HR\JobPosition');
    }

    protected static function booted() {
        static::created(
            function ($employee) {


                if(!$employee->legacy_id){
                    $employee->user()->create(
                        [
                            'handle'    => Str::slug($employee->name),
                            'tenant_id' => $employee->tenant_id,
                            'password'  => (env('APP_ENV', 'production') == 'devel' ? Hash::make('password') : Hash::make(Str::random(40))),
                            'pin'       => (env('APP_ENV', 'production') == 'devel' ? Hash::make('1234') : Hash::make(Str::random(6))),
                            'status'    => $employee->status == 'Working',
                            'settings'  => [],
                            'data'      => []

                        ]

                    );
                }





            }
        );
    }


}

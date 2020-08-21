<?php
/*
Author: Raul Perusquia (raul@inikoo.com)
Created:  Mon Jul 27 2020 19:53:46 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/


namespace App\Models\HR;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Cviebrock\EloquentSluggable\Sluggable;


/**
 * App\Models\HR\Employee
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\HR\Timesheet[] $timesheets
 * @property-read int|null                                                            $timesheets_count
 * @property-read \App\User|null                                                      $user
 * @method static Builder|Employee findSimilarSlugs($attribute, $config, $slug)
 * @method static Builder|Employee newModelQuery()
 * @method static Builder|Employee newQuery()
 * @method static Builder|Employee query()
 * @mixin \Eloquent
 */
class Employee extends Model {
    use UsesTenantConnection, Sluggable;

        protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
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


                $employee->user()->create(
                    [
                        'handle'    => Str::slug($employee->name),
                        'tenant_id' => $employee->tenant_id,
                        'password'  => (env('APP_ENV', 'production') == 'devel' ? Hash::make('password') : Hash::make(Str::random(40))),
                        'legacy_id' => $employee->legacy_id,
                        //'userable_type' => 'App\Employee',
                        //'userable_id'   => $employee->id,
                        'status'    => ($employee->status == 'Working' ? 'Active' : 'Disabled'),
                        'settings'  => [],
                        'data'      => []

                    ]

                );

            }
        );
    }


}

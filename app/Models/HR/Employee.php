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
 * @property int $id
 * @property string $name
 * @property int $user_id
 * @property array $data

 * @property \App\User $user


 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Employee extends Model implements Auditable {
    use UsesTenantConnection, Sluggable;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];


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
        //return $this->morphOne('App\User', 'userable');
        return $this->belongsTo('App\User', 'user_id');

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
                            'userable_type'    => 'Employee',
                            'userable_id' => $employee->id,
                            'password'  => (config('app.env') == 'devel' ? Hash::make('password') : Hash::make(Str::random(40))),
                            'pin'       => (config('app.env') == 'devel' ? Hash::make('1234') : Hash::make(Str::random(6))),
                            'status'    => ($employee->status == 'Working'?'active':'suspended'),
                            'settings'  => [],
                            'data'      => []

                        ]

                    );
                }





            }
        );
    }

    public function image() {
        return $this->morphOne('App\Models\Helpers\ImageModel', 'image_models','imageable_type','imageable_id');
    }

    public function attachments() {
        return $this->morphMany('App\Models\Helpers\AttachmentModel', 'attachment_models', 'attachmentable_type', 'attachmentable_id');
    }

}

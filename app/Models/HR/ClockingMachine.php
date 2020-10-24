<?php
/*
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Models\HR;

use App\Models\Helpers\AccessCode;
use App\User;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Models\HR\ClockingMachine
 * @property string $slug

 * @method static Builder|ClockingMachine findSimilarSlugs($attribute, $config, $slug)
 * @method static Builder|ClockingMachine newModelQuery()
 * @method static Builder|ClockingMachine newQuery()
 * @method static Builder|ClockingMachine query()
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class ClockingMachine extends Model implements Auditable{

    use UsesTenantConnection, Sluggable;
    use \OwenIt\Auditing\Auditable;


    public function sluggable() {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}'
    ];

    protected $fillable = ['name'];

    protected static function booted() {
        static::created(
            function ($clockingMachine) {


                $clockingMachine->create_access_code();



            }
        );
    }


    public function create_access_code(){


        $user = auth()->user();
        if($user==null){
            $user= (new User)->firstWhere('userable_type', 'Admin');
        }



        $tenant=app('currentTenant');

        $accessCode = new AccessCode;

        $accessCode->code = Str::random(6);
        $accessCode->tenant_id = $tenant->id;
        $accessCode->scope = 'ClockingMachine';
        $accessCode->expired_at       = gmdate('Y-m-d H:i:s',strtotime('now +300 86400'));

        $accessCode->scope_id = $this->getAttribute('id');
        $accessCode->payload=[
            'url'=>$tenant->slug.'.'.config('domain'),
            'user_id'=>($user==null?0:$user->getAttribute('id'))
        ];

        $accessCode->save();


        $this->state='waitingForDevice';
        $data=$this->getAttribute('data');
        $data['access_code']=[
           'id'=> $accessCode->id,
           'code'=>$accessCode->code,
           'expires'=>gmdate('Y-m-d H:i:s',strtotime($accessCode->created_at.' +'.$accessCode->ttl.' seconds'))
        ];
        $this->data=$data;
        $this->save();


    }

}

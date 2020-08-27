<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 27 Aug 2020 00:58:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class UserAgent extends Model {
    use UsesLandLordConnection;

    protected $fillable = ['checksum'];


    protected static function booted() {
        static::created(

            function ($userAgent) {

                if($userAgent->status=='InProcess'){
                    $userAgent->fetch_user_agent_device_info();
                }

            }
        );
    }

    public function fetch_user_agent_device_info(){
        //todo get data from external API
    }

}

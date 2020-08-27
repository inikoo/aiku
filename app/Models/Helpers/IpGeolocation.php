<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Wed, 26 Aug 2020 23:46:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandLordConnection;

class IpGeolocation extends Model {
    use UsesLandLordConnection;

    protected $fillable = ['ip'];


    protected static function booted() {
        static::created(
            function ($ipGeolocation) {

                    if($ipGeolocation->status=='InProcess'){
                        $ipGeolocation->fetch_ip_geolocation();
                    }

            }
        );
    }

    public function fetch_ip_geolocation(){
        //todo get data from external API

        /*

        shuffle($api_keys);
        $access_key = reset($api_keys);

        // Initialize CURL:
        $ch = curl_init('http://api.ipstack.com/'.$ip.'?access_key='.$access_key.'');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Store the data:
        $json = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response:
        $api_result = json_decode($json, true);

        */

    }

}

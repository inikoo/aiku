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
    }

}

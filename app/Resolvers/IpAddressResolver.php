<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Wed, 26 Aug 2020 23:28:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Resolvers;

use App\Models\Helpers\IpGeolocation;
use Illuminate\Support\Facades\Request;

class IpAddressResolver implements \OwenIt\Auditing\Contracts\IpAddressResolver
{
    /**
     * {@inheritdoc}
     */
    public static function resolve(): string
    {

        $ipGeolocation = IpGeolocation::firstOrCreate(
            ['ip' => Request::ip()],[]
        );



        return $ipGeolocation->id;
    }
}

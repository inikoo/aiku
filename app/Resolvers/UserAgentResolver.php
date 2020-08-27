<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 27 Aug 2020 01:10:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Resolvers;

use App\Models\Helpers\UserAgent;
use Illuminate\Support\Facades\Request;

class UserAgentResolver implements \OwenIt\Auditing\Contracts\UserAgentResolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve() {


        $userAgent = UserAgent::firstOrCreate(
            ['checksum' => md5(strtolower(Request::header('User-Agent')))], ['user_agent' => Request::header('User-Agent')]
        );


        return $userAgent->id;
    }
}

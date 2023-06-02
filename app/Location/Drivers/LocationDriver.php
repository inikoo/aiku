<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 02 Jun 2023 17:00:39 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Location\Drivers;

use Illuminate\Support\Fluent;
use Stevebauman\Location\Position;
use Stevebauman\Location\Drivers\Driver;

class LocationDriver extends Driver
{
    public function url($ip): string
    {
        return "http://driver-url.com?ip=$ip";
    }

    protected function process($ip)
    {
        return rescue(function () use ($ip) {
            $response = json_decode(file_get_contents($this->url($ip)), true);

            return new Fluent($response);
        }, $rescue = false);
    }

    protected function hydrate(Position $position, Fluent $location): Position
    {
        $position->countryCode = $location->country_code;

        return $position;
    }
}

<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jul 2023 13:31:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\UserRequest;

use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Stevebauman\Location\Facades\Location as FacadesLocation;

trait WithLocationDetector
{
    use AsAction;
    use WithAttributes;

    public function getLocation(string|null $ip): array
    {
        if ($position = FacadesLocation::get($ip)) {
            return [
                $position->countryCode,
                $position->countryName,
                $position->cityName
            ];
        }

        return [];
    }
}

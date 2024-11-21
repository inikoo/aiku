<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Nov 2024 10:24:50 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Analytics\UserRequest;

use App\Actions\GrpAction;
use App\Actions\Helpers\UniversalSearch\Trait\WithSectionsRoute;
use App\Actions\SysAdmin\User\StoreUserRequest;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Analytics\UserRequest;
use App\Models\SysAdmin\User;
use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Support\Carbon;
use Stevebauman\Location\Facades\Location as FacadesLocation;

class ProcessUserRequest extends GrpAction
{
    use WithNoStrictRules;
    use WithSectionsRoute;

    /**
     * @throws \Throwable
     */
    public function handle(User $user, Carbon $datetime, array $routeData, string $ip, string $userAgent): UserRequest
    {
        $parsedUserAgent = (new Browser())->parse($userAgent);
        $modelData = [
            'date'          => $datetime,
            'route_name'    => $routeData['name'],
            'route_params'  => json_encode($routeData['arguments']),
            'section'       => $this->parseSections($routeData['name']),
            'os'            => $this->detectWindows11($parsedUserAgent),
            'device'        => $parsedUserAgent->deviceType(),
            'browser'       => explode(' ', $parsedUserAgent->browserName())[0],
            'ip_address'    => $ip,
            'location'      => json_encode($this->getLocation($ip))
        ];

        $userRequest = StoreUserRequest::make()->action($user, $modelData);
        return $userRequest;
    }

    public function getLocation(string|null $ip): false|array|null
    {
        if ($position = FacadesLocation::get($ip == '127.0.0.1' ? '103.121.18.96' : $ip)) {
            return [
                $position->countryCode,
                $position->countryName,
                $position->cityName
            ];
        }

        return false;
    }

    public function detectWindows11($parsedUserAgent): string
    {
        if ($parsedUserAgent->isWindows()) {
            if (str_contains($parsedUserAgent->userAgent(), 'Windows NT 10.0; Win64; x64')) {
                return 'Windows 11';
            }

            return 'Windows 10';
        }

        return $parsedUserAgent->platformName();
    }

}

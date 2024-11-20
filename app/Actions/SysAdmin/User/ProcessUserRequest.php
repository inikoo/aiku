<?php
/*
 * author Arya Permana - Kirin
 * created on 20-11-2024-16h-54m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\SysAdmin\User;

use App\Actions\GrpAction;
use App\Actions\Helpers\UniversalSearch\Trait\WithSectionsRoute;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\SysAdmin\User;
use App\Models\SysAdmin\UserRequest;
use Stevebauman\Location\Facades\Location as FacadesLocation;
use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Support\Carbon;

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

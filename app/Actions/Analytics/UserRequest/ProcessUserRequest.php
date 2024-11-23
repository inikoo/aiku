<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Nov 2024 10:24:50 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Analytics\UserRequest;

use App\Actions\Analytics\GetSectionRoute;
use App\Actions\GrpAction;
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

    /**
     * @throws \Throwable
     */
    public function handle(User $user, Carbon $datetime, array $routeData, string $ip, string $userAgent): UserRequest|null
    {
        $section = GetSectionRoute::dispatch($routeData['name'], $routeData['arguments']);
        $aiku_scoped_section_id = $section?->id ?? null;


        $parsedUserAgent = (new Browser())->parse($userAgent);
        $modelData = [
            'date'                   => $datetime,
            'route_name'             => $routeData['name'],
            'route_params'           => json_encode($routeData['arguments']),
            'aiku_scoped_section_id' => $aiku_scoped_section_id,
            'os'                     => $this->detectWindows11($parsedUserAgent),
            'device'                 => $parsedUserAgent->deviceType(),
            'browser'                => explode(' ', $parsedUserAgent->browserName())[0],
            'ip_address'             => $ip,
            'location'               => json_encode($this->getLocation($ip))
        ];

        return StoreUserRequest::make()->action($user, $modelData);
    }

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

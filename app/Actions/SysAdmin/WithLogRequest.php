<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 11 Oct 2023 17:06:28 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin;

use App\Actions\Elasticsearch\IndexElasticsearchDocument;
use App\Enums\Elasticsearch\ElasticsearchUserRequestTypeEnum;
use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Support\Carbon;
use Stevebauman\Location\Facades\Location;

trait WithLogRequest
{
    public function getDeviceIcon($deviceType): string
    {
        if ($deviceType == 'Desktop') {
            return 'far fa-desktop-alt';
        }

        return 'fas fa-mobile-alt';
    }

    public function getBrowserIcon($browser): string
    {
        if (explode(' ', $browser)[0] == 'chrome') {
            return 'fab fa-chrome';
        } else {
            if ($browser == 'microsoft') {
                return 'fab fa-edge';
            }
        }

        return 'fab fa-firefox-browser';
    }

    public function getPlatformIcon($platform): string
    {
        if ($platform == 'android') {
            return 'fab fa-android';
        } else {
            if ($platform == 'apple') {
                return 'fab fa-apple';
            }
        }

        return 'fab fa-windows';
    }

    public function getLocation(string $ip): false|array|null
    {
        if ($position = Location::get($ip == '127.0.0.1' ? '103.121.18.96' : $ip)) {
            return [
                $position->countryCode,
                $position->countryName,
                $position->cityName
            ];
        }

        return false;
    }

    // if platform=='Windows 10' need to check if it is actually Windows 11 see:
    // https://developers.whatismybrowser.com/learn/browser-detection/client-hints/detect-windows-11-client-hints
    // https://stackoverflow.com/questions/68614445/how-to-detect-windows-11-from-user-agent

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


    public function logFail(string $index, Carbon $datetime, string $ip, string $userAgent, string $username, ?int $userID): void
    {
        $index = config('elasticsearch.index_prefix').$index;

        $parsedUserAgent = (new Browser())->parse($userAgent);

        $body = [
            'type'                 => ElasticsearchUserRequestTypeEnum::FAIL_LOGIN->value,
            'datetime'             => $datetime,
            'username'             => $username,
            'organisation_user_id' => $userID,
            'ip_address'           => $ip,
            'location'             => json_encode($this->getLocation($ip)), // reference: https://github.com/stevebauman/location
            'user_agent'           => $userAgent,
            'device_type'          => json_encode([
                'title' => $parsedUserAgent->deviceType(),
                'icon'  => $this->getDeviceIcon($parsedUserAgent->deviceType())
            ]),
            'platform'             => json_encode([
                'title' => $this->detectWindows11($parsedUserAgent),
                'icon'  => $this->getPlatformIcon($this->detectWindows11($parsedUserAgent))
            ]),
            'browser'              => json_encode([
                'title' => explode(' ', $parsedUserAgent->browserName())[0],
                'icon'  => $this->getBrowserIcon(strtolower($parsedUserAgent->browserName()))
            ])
        ];

        IndexElasticsearchDocument::run(index: $index, body: $body);
    }

}

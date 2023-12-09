<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 18 Oct 2023 12:12:02 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\Elasticsearch\IndexElasticsearchDocument;
use App\Actions\SysAdmin\WithLogRequest;
use App\Models\SysAdmin\User;
use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Support\Carbon;

trait WithAuthUserLog
{
    use WithLogRequest;

    public function logAuthUserAction($type, Carbon $datetime, string $ip, string $userAgent, User $user): void
    {
        $index = config('elasticsearch.index_prefix').'users_requests';

        $parsedUserAgent = (new Browser())->parse($userAgent);

        $body = [
            'type'                 => $type,
            'datetime'             => $datetime,
            'username'             => $user->username,
            'organisation_user_id' => $user->id,
            'ip_address'           => $ip,
            'location'             => json_encode($this->getLocation($ip)), // reference: https://github.com/stevebauman/location
            'user_agent'           => $userAgent,
            'device_type'          => json_encode([
                'title' => $parsedUserAgent->deviceType(),
                'icon'  => $this->getDeviceIcon($parsedUserAgent->deviceType())
            ]),
            'platform'           => json_encode([
                'title' => $this->detectWindows11($parsedUserAgent),
                'icon'  => $this->getPlatformIcon($this->detectWindows11($parsedUserAgent))
            ]),
            'browser'            => json_encode([
                'title' => explode(' ', $parsedUserAgent->browserName())[0],
                'icon'  => $this->getBrowserIcon(strtolower($parsedUserAgent->browserName()))
            ])
        ];

        IndexElasticsearchDocument::dispatch(index: $index, body: $body);
    }
}

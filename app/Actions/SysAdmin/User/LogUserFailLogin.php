<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 18 Oct 2023 12:34:29 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\Elasticsearch\IndexElasticsearchDocument;
use App\Actions\SysAdmin\WithLogRequest;
use App\Enums\Elasticsearch\ElasticsearchUserRequestTypeEnum;
use App\Models\SysAdmin\User;
use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class LogUserFailLogin
{
    use AsAction;
    use WithLogRequest;

    public function handle(array $credentials, string $ip, string $userAgent, Carbon $datetime): void
    {

        $user = User::withTrashed()->where('username', Arr::get($credentials, 'username'))->first();

        $this->log($datetime, $ip, $userAgent, Arr::get($credentials, 'username'), $user?->id);


        if ($user) {
            $stats = [
                'number_failed_logins' => $user->stats->number_failed_logins + 1,
                'last_failed_login_ip' => $ip,
                'last_failed_login_at' => $datetime
            ];

            $user->stats()->update($stats);

        }

    }


    public function log(Carbon $datetime, string $ip, string $userAgent, string $username, ?int $userID): void
    {
        $index = config('elasticsearch.index_prefix').'users_requests';

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

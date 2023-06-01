<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 14:38:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use App\Actions\ElasticSearch\IndexElasticsearchDocument;
use App\Actions\WithTenantJob;
use App\Models\Auth\User;
use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class LogUserRequest
{
    use AsAction;
    use WithTenantJob;

    public function handle(Carbon $datetime, array $routeData, string $ip, string $userAgent, User $user): void
    {
        $tenant = app('currentTenant');

        $index = 'user_requests_'.$tenant->group->slug;


        $parsedUserAgent = (new Browser())->parse($userAgent);


        $body = [
            'datetime'    => $datetime,
            'tenant'      => $tenant->slug,
            'username'    => $user->username,
            'route'       => $routeData,
            'ip_address'  => $ip,
            'location'    => '',// todo get it using https://github.com/stevebauman/location
            'user_agent'  => $userAgent,
            'device_type' => $parsedUserAgent->deviceType(),
            'platform'    => $parsedUserAgent->platformName(),
            'browser'     => $parsedUserAgent->browserName()

        ];

        // if platform=='Windows 10' need to check if it is actually Windows 11 see:
        // https://developers.whatismybrowser.com/learn/browser-detection/client-hints/detect-windows-11-client-hints
        // https://stackoverflow.com/questions/68614445/how-to-detect-windows-11-from-user-agent


        IndexElasticsearchDocument::run(index: $index, body: $body);
    }
}

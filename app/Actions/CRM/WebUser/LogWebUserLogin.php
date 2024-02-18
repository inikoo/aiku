<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Feb 2024 10:40:25 CST, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUser;

use App\Actions\Traits\WithLogUserableLogin;
use App\Enums\Elasticsearch\ElasticsearchUserRequestTypeEnum;
use App\Models\CRM\WebUser;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class LogWebUserLogin
{
    use AsAction;
    use WithLogUserableLogin;


    public function handle(WebUser $webUser, string $ip, string $userAgent, Carbon $datetime): void
    {
        $this->logUserableLogin(
            config('elasticsearch.index_prefix').'web_users_requests',
            ElasticsearchUserRequestTypeEnum::LOGIN->value,
            $datetime,
            $ip,
            $userAgent,
            $webUser
        );

        $stats = [
            'last_login_at' => $datetime,
            'last_login_ip' => $ip,
            'number_logins' => $webUser->stats->number_logins + 1
        ];

        $webUser->stats()->update($stats);


    }


}

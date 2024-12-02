<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailAddress;

use App\Actions\Comms\EmailAddress\Traits\AwsClient;
use App\Actions\Web\Website\Utils\DeleteDomainDnsRecordCloudflare;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteDomainEmailAddress
{
    use AsAction;
    use AwsClient;

    public string $commandSignature   = 'dns:delete {domain} {zone}';
    public string $commandDescription = 'Delete domain In AWS to Cloudflare';

    public function handle(string $domain, string $zone): void
    {
        $this->getSesClient()->deleteIdentity([
            'Identity' => $domain
        ]);

        DeleteDomainDnsRecordCloudflare::run($zone, []);
    }

    public function asCommand(Command $command): void
    {
        $this->handle($command->argument('domain'), $command->argument('zone'));
    }
}

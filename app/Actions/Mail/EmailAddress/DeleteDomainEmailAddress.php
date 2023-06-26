<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 01:05:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailAddress;

use App\Actions\Mail\EmailAddress\Traits\AwsClient;
use App\Actions\Mail\EmailAddress\Traits\WithCloudflareDns;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteDomainEmailAddress
{
    use AsAction;
    use AwsClient;
    use WithCloudflareDns;

    public string $commandSignature   = 'domain:delete {domain}';
    public string $commandDescription = 'Delete Domain In AWS to Cloudflare';

    public function handle(string $domain): void
    {
        $this->getSesClient()->deleteIdentity([
            'Identity' => $domain,
        ]);

        $this->deleteDnsRecords(env('CLOUDFLARE_ZONE_ID'), ['004fafe1450900392c8d541a686e594a']);
    }

    public function asCommand(Command $command): void
    {
        $this->handle($command->argument('domain'));
    }
}

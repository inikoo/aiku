<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 01:05:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailAddress;

use App\Actions\Mail\EmailAddress\Traits\AwsClient;
use App\Actions\Mail\EmailAddress\Traits\WithCloudflareDns;
use App\Actions\Web\Domain\DeleteDomainDnsRecord;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteDomainEmailAddress
{
    use AsAction;
    use AwsClient;
    use WithCloudflareDns;

    public string $commandSignature   = 'dns:delete {domain} {zone}';
    public string $commandDescription = 'Delete domain In AWS to Cloudflare';

    public function handle(string $domain, string $zone): void
    {
        $this->getSesClient()->deleteIdentity([
            'Identity' => $domain
        ]);

        DeleteDomainDnsRecord::run($zone, []);
    }

    public function asCommand(Command $command): void
    {
        $this->handle($command->argument('domain'), $command->argument('zone'));
    }
}

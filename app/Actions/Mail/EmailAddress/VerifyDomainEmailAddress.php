<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 01:05:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailAddress;

use App\Actions\Mail\EmailAddress\Traits\AwsClient;
use App\Actions\Web\Domain\AddDomainDnsRecordCloudflare;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class VerifyDomainEmailAddress
{
    use AsAction;
    use AwsClient;

    public string $commandSignature   = 'domain:verify {domain} {zone}';
    public string $commandDescription = 'Verify Domain In AWS to Cloudflare';

    public function handle(string $domain, string $zoneId): void
    {
        $result = $this->getSesClient()->verifyDomainIdentity([
            'Domain' => $domain,
        ]);

        AddDomainDnsRecordCloudflare::run($zoneId, [
            [
                'type' => 'TXT',
                'name' => $domain,
                'content' => $result['VerificationToken'],
                'proxied' => false
            ],
            [
                'type' => 'A',
                'name' => $domain,
                'content' => '65.109.156.41',
                'proxied' => true
            ]
        ]);
    }

    public function asCommand(Command $command): void
    {
        $this->handle($command->argument('domain'), $command->argument('zone'));
    }
}

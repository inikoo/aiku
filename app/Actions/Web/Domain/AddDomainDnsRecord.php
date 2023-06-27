<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 27 Jun 2023 13:36:43 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Domain;

use App\Actions\Mail\EmailAddress\Traits\WithCloudflareDns;
use Lorisleiva\Actions\Concerns\AsAction;

class AddDomainDnsRecord
{
    use AsAction;
    use WithCloudflareDns;

    public string $commandSignature   = 'dns:delete {domain}';
    public string $commandDescription = 'Delete dns from Cloudflare';

    public function handle(string $zoneId, array $dnsRecords): void
    {
        $this->addDnsRecords($zoneId, $dnsRecords);
    }
}

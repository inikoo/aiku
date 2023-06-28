<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 27 Jun 2023 08:35:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Central;

use App\Actions\Web\Domain\RegisterDomainCloudflare;
use App\Models\Central\Domain;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class AddDomainToCloudflare
{
    use AsAction;

    public string $commandSignature   = 'domain:add-cloudflare {domain}';
    public string $commandDescription = 'Register Domain to Cloudflare';

    public function handle(Domain $domain): string
    {
        $response = RegisterDomainCloudFlare::run($domain->domain);

        $domain->update([
            'cloudflare_id'     => $response['result']['id'],
            'cloudflare_status' => $response['result']['status'],
        ]);

        return $domain;
    }

    public function asCommand(Command $command): string
    {
        $domain = Domain::where('domain', $command->argument('domain'))->first();

        return $this->handle($domain);
    }
}

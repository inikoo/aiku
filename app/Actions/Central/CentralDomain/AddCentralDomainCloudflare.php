<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 27 Jun 2023 08:35:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */



namespace App\Actions\Central\CentralDomain;

use App\Actions\Web\Domain\RegisterDomainCloudflare;
use App\Models\Central\CentralDomain;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class AddCentralDomainCloudflare
{
    use AsAction;

    public string $commandSignature   = 'domain:register {domain}';
    public string $commandDescription = 'Register Domain to Cloudflare';

    public function handle(CentralDomain $centralDomain): string
    {
        $response = RegisterDomainCloudFlare::run($centralDomain->domain);

        $centralDomain->update([
            'cloudflare_id' => $response['result']['id'],
            'cloudflare_status' => $response['result']['status'],
        ]);

        return $centralDomain;
    }

    public function asCommand(Command $command): string
    {
        $centralDomain = CentralDomain::where('domain', $command->argument('domain'))->first();

        return $this->handle($centralDomain);
    }
}

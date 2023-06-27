<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 27 Jun 2023 08:35:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */



namespace App\Actions\Central\CentralDomain;

use App\Actions\Web\Domain\DestroyDomainCloudflare;
use App\Models\Central\CentralDomain;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCentralDomainCloudflare
{
    use AsAction;

    public string $commandSignature   = 'domain:delete {domain}';
    public string $commandDescription = 'Delete domain from Cloudflare';

    public function handle(CentralDomain $centralDomain): string
    {
        DestroyDomainCloudflare::run($centralDomain->cloudflare_id);

        $centralDomain->update([
            'cloudflare_id' => null,
            'cloudflare_status' => null,
        ]);

        return $centralDomain;
    }

    public function asCommand(Command $command): string
    {
        $centralDomain = CentralDomain::where('domain', $command->argument('domain'))->first();

        return $this->handle($centralDomain);
    }
}

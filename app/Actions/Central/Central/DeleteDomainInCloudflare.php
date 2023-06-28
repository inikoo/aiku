<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 27 Jun 2023 08:35:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */



namespace App\Actions\Central\Central;

use App\Actions\Web\Domain\DestroyDomainCloudflare;
use App\Models\Central\Domain;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteDomainInCloudflare
{
    use AsAction;

    public string $commandSignature   = 'domain:delete {domain}';
    public string $commandDescription = 'Remove domain from Cloudflare';

    public function handle(Domain $domain): string
    {
        DestroyDomainCloudflare::run($domain->cloudflare_id);

        $domain->update([
            'cloudflare_id' => null,
            'cloudflare_status' => null,
        ]);

        return $domain;
    }

    public function asCommand(Command $command): string
    {
        $domain = Domain::where('domain', $command->argument('domain'))->first();

        return $this->handle($domain);
    }
}

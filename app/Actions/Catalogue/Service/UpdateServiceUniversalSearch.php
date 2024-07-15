<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 17:38:48 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Service;

use App\Actions\Catalogue\Service\Hydrators\ServiceHydrateUniversalSearch;
use App\Models\Catalogue\Service;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateServiceUniversalSearch
{
    use asAction;

    public string $commandSignature = 'services:search';

    public function handle(Service $service): void
    {
        ServiceHydrateUniversalSearch::run($service);
    }

    public function asCommand(Command $command): int
    {
        $command->withProgressBar(Service::all(), function (Service $service) {
            $this->handle($service);
        });
        return 0;
    }
}

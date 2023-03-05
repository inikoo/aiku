<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 04 Oct 2022 11:35:38 Central European Summer Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Deployment;

use App\Models\Central\Deployment;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreFirstDeployment
{
    use AsAction;

    public string $commandSignature = 'create:first-deployment';

    public function getCommandDescription(): string
    {
        return 'Create first deployment';
    }

    public function handle(array $modelData): Deployment
    {
        return StoreDeployment::run($modelData);
    }

    public function asCommand(Command $command): int
    {
        $this->handle([
                          'hash'    => StoreDeployment::make()->getCurrentHash(),
                          'state'   => 'deployed',
                          'version' => '0.1.0'
                      ]);

        $command->line('First deployment created.');

        return 0;
    }
}

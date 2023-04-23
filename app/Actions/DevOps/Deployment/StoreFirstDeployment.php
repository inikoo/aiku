<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:58 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\DevOps\Deployment;

use App\Models\DevOps\Deployment;
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

<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Apr 2023 08:40:49 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Group;

use Illuminate\Console\Command;
use App\Models\Central\Group;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreGroup {

    use AsAction;

    public string $commandSignature = 'create:group {code} {name}';

    public function handle(array $modelData)
    {
        return Group::create($modelData);
    }

    public function asCommand(Command $command): void
    {
        $this->handle(
            [
                'code' => $command->argument('code'),
                'name' => $command->argument('name'),
            ]
        );

        $command->info('Done!');
    }
}

<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Apr 2023 08:40:49 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Group;

use App\Models\Assets\Currency;
use Illuminate\Console\Command;
use App\Models\Central\Group;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreGroup {

    use AsAction;

    public string $commandSignature = 'create:group {code} {name} {currency_code}';

    public function handle(array $modelData)
    {
        return Group::create($modelData);
    }

    public function asCommand(Command $command): void
    {
        $currency = Currency::where('code', $command->argument('currency_code'))->first();
        $this->handle(
            [
                'code' => $command->argument('code'),
                'name' => $command->argument('name'),
                'currency_id' => $currency->id
            ]
        );

        $command->info('Done!');
    }
}

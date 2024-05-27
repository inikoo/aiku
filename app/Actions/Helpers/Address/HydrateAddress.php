<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Feb 2023 13:01:38 Malaysia Time, Ubud
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Address;

use App\Actions\Helpers\Address\Hydrators\AddressHydrateFixedUsage;
use App\Actions\Helpers\Address\Hydrators\AddressHydrateMultiplicity;
use App\Actions\Helpers\Address\Hydrators\AddressHydrateUsage;
use App\Actions\HydrateModel;
use App\Models\Helpers\Address;
use Illuminate\Console\Command;

class HydrateAddress extends HydrateModel
{
    public string $commandSignature = 'address:hydrate {--id=}';

    public function handle(Address $address): void
    {
        AddressHydrateUsage::run($address);
        AddressHydrateMultiplicity::run($address);
        AddressHydrateFixedUsage::run($address);
    }

    public function asCommand(Command $command): int
    {
        $exitCode = 0;
        if ($command->option('id')) {
            $address = Address::find($command->option('id'));
            $this->handle($address);
        } else {
            $command->withProgressBar(Address::all(), function ($address) {
                if ($address) {
                    $this->handle($address);
                }
            });
            $command->info("");
        }

        return $exitCode;
    }
}

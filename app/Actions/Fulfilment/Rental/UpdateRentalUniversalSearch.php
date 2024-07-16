<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 17:38:48 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Rental;

use App\Actions\Fulfilment\Rental\Hydrators\RentalHydrateUniversalSearch;
use App\Models\Fulfilment\Rental;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateRentalUniversalSearch
{
    use asAction;

    public string $commandSignature = 'rentals:search';

    public function handle(Rental $rental): void
    {
        RentalHydrateUniversalSearch::run($rental);
    }

    public function asCommand(Command $command): int
    {
        $command->withProgressBar(Rental::all(), function (Rental $rental) {
            $this->handle($rental);
        });
        return 0;
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 14:06:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Rental\Search;

use App\Models\Fulfilment\Rental;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class ReindexRentalSearch
{
    use asAction;

    public string $commandSignature = 'rentals:search';

    public function handle(Rental $rental): void
    {
        RentalRecordSearch::run($rental);
    }

    public function asCommand(Command $command): int
    {
        $command->withProgressBar(Rental::withTrashed()->all(), function (Rental $rental) {
            $this->handle($rental);
        });

        return 0;
    }
}

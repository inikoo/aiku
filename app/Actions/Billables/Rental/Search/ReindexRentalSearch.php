<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Nov 2024 20:58:56 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Billables\Rental\Search;

use App\Models\Billables\Rental;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class ReindexRentalSearch
{
    use asAction;

    public string $commandSignature = 'search:rentals';

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

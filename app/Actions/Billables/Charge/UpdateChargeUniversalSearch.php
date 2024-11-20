<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 20 Nov 2024 15:21:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Billables\Charge;

use App\Actions\Billables\Charge\Hydrators\ChargeHydrateUniversalSearch;
use App\Models\Billables\Charge;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateChargeUniversalSearch
{
    use asAction;

    public string $commandSignature = 'charges:search';

    public function handle(Charge $charge): void
    {
        ChargeHydrateUniversalSearch::run($charge);
    }

    public function asCommand(Command $command): int
    {
        $command->withProgressBar(Charge::all(), function (Charge $charge) {
            $this->handle($charge);
        });
        return 0;
    }
}

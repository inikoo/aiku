<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 17:38:48 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

 namespace App\Actions\Catalogue\Charge;

use App\Actions\Catalogue\Charge\Hydrators\ChargeHydrateUniversalSearch;
use App\Actions\SupplyChain\Supplier\Hydrators\SupplierHydrateUniversalSearch;
use App\Models\Catalogue\Charge;
use App\Models\SupplyChain\Supplier;
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

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 17:38:48 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Collection;

use App\Actions\Catalogue\Charge\Hydrators\ChargeHydrateUniversalSearch;
use App\Actions\Catalogue\Collection\Hydrators\CollectionHydrateUniversalSearch;
use App\Actions\Fulfilment\Rental\Hydrators\RentalHydrateUniversalSearch;
use App\Actions\SupplyChain\Supplier\Hydrators\SupplierHydrateUniversalSearch;
use App\Models\Catalogue\Charge;
use App\Models\Catalogue\Collection;
use App\Models\Fulfilment\Rental;
use App\Models\SupplyChain\Supplier;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCollectionUniversalSearch
{
    use asAction;

    public string $commandSignature = 'collections:search';

    public function handle(Collection $collection): void
    {
        CollectionHydrateUniversalSearch::run($collection);
    }

    public function asCommand(Command $command): int
    {
        $command->withProgressBar(Collection::all(), function (Collection $collection) {
            $this->handle($collection);
        });
        return 0;
    }
}

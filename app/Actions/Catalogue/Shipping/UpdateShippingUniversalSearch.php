<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 17:38:48 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shipping;

use App\Actions\Catalogue\Charge\Hydrators\ChargeHydrateUniversalSearch;
use App\Actions\Catalogue\Shipping\Hydrators\ShippingHydrateUniversalSearch;
use App\Models\Catalogue\Charge;
use App\Models\Catalogue\Shipping;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateShippingUniversalSearch
{
    use asAction;

    public string $commandSignature = 'shippings:search';

    public function handle(Shipping $shipping): void
    {
        ShippingHydrateUniversalSearch::run($shipping);
    }

    public function asCommand(Command $command): int
    {
        $command->withProgressBar(Shipping::all(), function (Shipping $shipping) {
            $this->handle($shipping);
        });
        return 0;
    }
}

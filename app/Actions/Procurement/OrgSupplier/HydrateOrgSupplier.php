<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Aug 2024 15:16:23 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplier;

use App\Actions\Procurement\OrgSupplier\Hydrators\OrgSupplierHydrateOrgSupplierProducts;
use App\Actions\Procurement\OrgSupplier\Hydrators\OrgSupplierHydratePurchaseOrders;
use App\Models\Procurement\OrgSupplier;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class HydrateOrgSupplier
{
    use asAction;

    public string $commandSignature = 'hydrate:org_suppliers';

    public function handle(OrgSupplier $orgSupplier): void
    {
        OrgSupplierHydrateOrgSupplierProducts::run($orgSupplier);
        OrgSupplierHydratePurchaseOrders::run($orgSupplier);

    }

    public function asCommand(Command $command): int
    {

        $command->withProgressBar(OrgSupplier::all(), function (OrgSupplier $orgSupplier) {
            $this->handle($orgSupplier);
        });

        return 0;
    }
}

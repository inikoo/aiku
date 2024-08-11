<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Aug 2024 14:29:31 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplier\Hydrators;

use App\Models\Procurement\OrgSupplier;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgSupplierHydrateOrgSupplierProducts
{
    use AsAction;

    private OrgSupplier $orgSupplier;


    public function __construct(OrgSupplier $orgSupplier)
    {
        $this->orgSupplier = $orgSupplier;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->orgSupplier->id))->dontRelease()];
    }


    public function handle(OrgSupplier $orgSupplier): void
    {
        $stats = [
            'number_org_supplier_products'             => $orgSupplier->orgSupplierProducts()->count(),
            'number_current_org_supplier_products'     => $orgSupplier->orgSupplierProducts()->where('status', true)->count(),
        ];

        $orgSupplier->stats()->update($stats);
    }


}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Aug 2024 12:16:37 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent\Hydrators;

use App\Actions\Traits\Hydrators\WithHydrateOrgSuppliers;
use App\Models\Procurement\OrgAgent;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgAgentHydrateOrgSuppliers
{
    use AsAction;
    use WithHydrateOrgSuppliers;

    private OrgAgent $orgAgent;

    public function __construct(OrgAgent $orgAgent)
    {
        $this->orgAgent = $orgAgent;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->orgAgent->id))->dontRelease()];
    }

    public function handle(OrgAgent $orgAgent): void
    {

        $stats=$this->getOrgSuppliersStats($orgAgent);
        $orgAgent->stats()->update($stats);
    }


}

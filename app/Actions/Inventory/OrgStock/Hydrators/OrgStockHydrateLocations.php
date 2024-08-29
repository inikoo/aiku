<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 02 Jun 2023 20:55:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\Hydrators;

use App\Models\Inventory\OrgStock;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgStockHydrateLocations
{
    use AsAction;

    private OrgStock $orgStock;

    public function __construct(OrgStock $orgStock)
    {
        $this->orgStock = $orgStock;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->orgStock->id))->dontRelease()];
    }


    public function handle(OrgStock $orgStock): void
    {

        $orgStock->stats->update(
            [
                'number_locations' => $orgStock->locationOrgStocks()->count()
            ]
        );
    }


}

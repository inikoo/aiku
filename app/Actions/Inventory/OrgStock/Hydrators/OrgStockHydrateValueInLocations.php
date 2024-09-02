<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 02 Jun 2023 21:00:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\Hydrators;

use App\Models\Inventory\OrgStock;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgStockHydrateValueInLocations
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

        $orgStock->update([
            'value_in_locations' => $orgStock->locationOrgStocks()->sum('quantity')*$orgStock->unit_value
        ]);
    }


}

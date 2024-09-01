<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 31 Aug 2024 10:58:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\Hydrators;

use App\Models\Inventory\OrgStock;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgStockHydrateMovements
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
                'number_movements' => $orgStock->orgStockMovements()->count()
            ]
        );
    }


}

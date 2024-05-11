<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 15 Nov 2023 15:04:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Models\Helpers\Query;
use App\Models\Catalogue\Shop;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateQueries
{
    use AsAction;


    public function handle(Shop $shop): void
    {
        $stats = [
            'number_customer_queries'  => Query::where('parent_type', 'Shop')->where('parent_id', $shop->id)->where('model_type', 'Customer')->count(),
            'number_prospect_queries'  => Query::where('parent_type', 'Shop')->where('parent_id', $shop->id)->where('model_type', 'Prospect')->count(),
        ];


        $shop->crmStats()->update($stats);
    }


}

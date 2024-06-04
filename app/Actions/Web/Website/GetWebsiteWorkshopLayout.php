<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 04 Jun 2024 16:24:39 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsObject;

class GetWebsiteWorkshopLayout
{
    use AsObject;

    public function handle(Shop|Fulfilment $scope, Website $website): array
    {
        if($scope instanceof Fulfilment) {


            $workshopRoutes=[
                'headerRoute' => [
                    'name'       => 'grp.org.fulfilments.show.web.websites.workshop.header',
                    'parameters' => [
                        'organisation' => $website->organisation->slug,
                        'fulfilment'   => $website->shop->fulfilment->slug,
                        'website'      => $website->slug,
                    ]
                ],
                'footerRoute' => [
                    'name'       => 'grp.org.fulfilments.show.web.websites.workshop.footer',
                    'parameters' => [
                        'organisation' => $website->organisation->slug,
                        'fulfilment'   => $website->shop->fulfilment->slug,
                        'website'      => $website->slug,
                    ]
                ]
            ];
        } else {
            $workshopRoutes=[
                'headerRoute' => [
                    'name'       => 'grp.org.shops.show.web.websites.workshop.header',
                    'parameters' => [
                        'organisation' => $website->organisation->slug,
                        'fulfilment'   => $website->shop->slug,
                        'website'      => $website->slug,
                    ]
                ],
                'footerRoute' => [
                    'name'       => 'grp.org.shops.show.web.websites.workshop.footer',
                    'parameters' => [
                        'organisation' => $website->organisation->slug,
                        'fulfilment'   => $website->shop->slug,
                        'website'      => $website->slug,
                    ]
                ]
            ];
        }

        return $workshopRoutes;
    }
}

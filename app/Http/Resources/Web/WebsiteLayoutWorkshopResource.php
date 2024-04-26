<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jul 2023 12:17:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Web;

use App\Http\Resources\HasSelfCall;
use Illuminate\Http\Resources\Json\JsonResource;

class WebsiteLayoutWorkshopResource extends JsonResource
{
    use HasSelfCall;
    public function toArray($request): array
    {
        $website = $this;

        return [
            'headerRoute' => [
                'name'       => 'grp.org.fulfilments.show.web.websites.workshop.header',
                'parameters' => [
                    'organisation' => $website->resource->organisation_id,
                    'fulfilment'   => $website->resource->shop->fulfilment->id,
                    'website'      => $website->resource->id,
                ]
            ],
            'footerRoute' => [
                'name'       => 'grp.org.fulfilments.show.web.websites.workshop.footer',
                'parameters' => [
                    'organisation' => $website->resource->organisation_id,
                    'fulfilment'   => $website->resource->shop->fulfilment->id,
                    'website'      => $website->resource->id,
                ]
            ]
        ];
    }
}

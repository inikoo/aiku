<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jul 2023 12:17:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Web;

use App\Actions\Web\Website\GetWebsiteWorkshopFooter;
use App\Actions\Web\Website\GetWebsiteWorkshopHeader;
use App\Http\Resources\HasSelfCall;
use Illuminate\Http\Resources\Json\JsonResource;

class WebsiteLayoutWorkshopResource extends JsonResource
{
    use HasSelfCall;
    public function toArray($request): array
    {
        $website = $this;

        return [
            'header' => GetWebsiteWorkshopHeader::run($website->resource),
            'footer' => GetWebsiteWorkshopFooter::run($website->resource)
        ];
    }
}

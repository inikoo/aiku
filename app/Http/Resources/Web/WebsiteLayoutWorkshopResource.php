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
use App\Models\Web\Website;
use Illuminate\Http\Resources\Json\JsonResource;

class WebsiteLayoutWorkshopResource extends JsonResource
{
    use HasSelfCall;
    public function toArray($request): array
    {
        /** @var Website $website */
        $website = $this;

        return [
            'header' => GetWebsiteWorkshopHeader::run($website),
            'footer' => GetWebsiteWorkshopFooter::run($website)
        ];
    }
}

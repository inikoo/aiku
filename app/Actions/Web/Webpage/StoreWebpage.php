<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 08:32:10 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Actions\Web\WebpageVariant\StoreWebpageVariant;
use App\Models\Web\Website;
use App\Models\Web\Webpage;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWebpage
{
    use AsAction;

    public function handle(Website $website, array $modelData, array $webpageVariantData): Webpage
    {
        /** @var Webpage $webpage */
        $webpage = $website->webpages()->create($modelData);
        $webpage->stats()->create();

        StoreWebpageVariant::run($webpage, $webpageVariantData);

        return $webpage;
    }
}

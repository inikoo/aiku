<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 08:32:10 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webnode;

use App\Actions\Web\Webpage\StoreWebpage;
use App\Models\Web\Website;
use App\Models\Web\Webnode;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreWebnode
{
    use AsAction;

    public function handle(Website $website, array $modelData,array $webpageData): Webnode
    {
        /** @var Webnode $webnode */
        $webnode = $website->webnodes()->create($modelData);
        $webnode->stats()->create();

        StoreWebpage::run($webnode,$webpageData);

        return $webnode;
    }


}

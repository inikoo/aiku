<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 13:53:53 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Models\Web\Webnode;
use App\Models\Web\Webpage;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWebpage
{
    use AsAction;

    public function handle(Webnode $webnode, array $modelData): Webpage
    {
        $modelData['code'] = $webnode->slug;

        /** @var Webpage $webpage */
        $webpage = $webnode->webpages()->create($modelData);
        $webpage->stats()->create();

        $webnode->update(
            [
                'main_webpage_id' => $webpage->id
            ]
        );

        return $webpage;
    }
}

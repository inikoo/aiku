<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 16:06:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\Hydrators;

use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsAction;

class WebsiteHydrateUniversalSearch
{
    use AsAction;


    public function handle(Website $website): void
    {
        $website->universalSearch()->updateOrCreate(
            [],
            [
                'section'     => 'web',
                'title'       => trim($website->code.' '.$website->name.' '.$website->domain),
                'description' => ''
            ]
        );
    }


}

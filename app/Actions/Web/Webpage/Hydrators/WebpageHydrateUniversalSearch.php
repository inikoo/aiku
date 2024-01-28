<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Jan 2024 10:32:58 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage\Hydrators;

use App\Models\Web\Webpage;
use Lorisleiva\Actions\Concerns\AsAction;

class WebpageHydrateUniversalSearch
{
    use AsAction;


    public function handle(Webpage $webpage): void
    {
        $webpage->universalSearch()->updateOrCreate(
            [],
            [
                'section'     => 'web',
                'title'       => trim($webpage->code.' '.$webpage->url),
                'description' => ''
            ]
        );
    }


}

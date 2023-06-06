<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 13:53:53 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebpageVariant;

use App\Models\Web\Webpage;
use App\Models\Web\WebpageVariant;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWebpageVariant
{
    use AsAction;

    public function handle(Webpage $webpage, array $modelData): WebpageVariant
    {
        $modelData['code'] = $webpage->slug;

        /** @var WebpageVariant $webpageVariant */
        $webpageVariant = $webpage->variants()->create($modelData);
        $webpageVariant->stats()->create();


        if(!$webpage->main_variant_id) {
            $webpage->update(
                [
                    'main_variant_id' => $webpageVariant->id
                ]
            );
        }
        return $webpageVariant;
    }
}

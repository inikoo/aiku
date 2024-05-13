<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Outer\Hydrators;

use App\Models\Catalogue\Outer;
use Lorisleiva\Actions\Concerns\AsAction;

class OuterHydrateUniversalSearch
{
    use AsAction;


    public function handle(Outer $outer): void
    {
        $outer->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $outer->group_id,
                'organisation_id'   => $outer->organisation_id,
                'organisation_slug' => $outer->organisation->slug,
                'shop_id'           => $outer->shop_id,
                'shop_slug'         => $outer->shop->slug,
                'section'           => 'shops',
                'title'             => $outer->name,
                'description'       => $outer->code
            ]
        );
    }

}

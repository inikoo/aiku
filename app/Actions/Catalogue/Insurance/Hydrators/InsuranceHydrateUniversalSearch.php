<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Insurance\Hydrators;

use App\Models\Catalogue\Insurance;
use Lorisleiva\Actions\Concerns\AsAction;

class InsuranceHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Insurance $insurance): void
    {
        $insurance->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $insurance->group_id,
                'organisation_id'   => $insurance->organisation_id,
                'organisation_slug' => $insurance->organisation->slug,
                'shop_id'           => $insurance->shop_id,
                'shop_slug'         => $insurance->shop->slug,
                'section'           => 'shops',
                'title'             => $insurance->name,
            ]
        );
    }

}

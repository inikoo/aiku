<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:45:00 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect\Hydrators;

use App\Models\CRM\Prospect;
use Lorisleiva\Actions\Concerns\AsAction;

class ProspectHydrateUniversalSearch
{
    use AsAction;


    public function handle(Prospect $prospect): void
    {
        $prospect->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $prospect->group_id,
                'organisation_id'   => $prospect->organisation_id,
                'organisation_slug' => $prospect->organisation->slug,
                'shop_id'           => $prospect->shop_id,
                'shop_slug'         => $prospect->shop->slug,
                'section'           => 'crm',
                'title'             => trim($prospect->name.' '.$prospect->contact_name),
                'description'       => trim($prospect->email.' '.$prospect->company_name)
            ]
        );
    }

}

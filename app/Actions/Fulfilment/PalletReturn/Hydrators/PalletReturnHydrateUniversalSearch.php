<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Fulfilment\PalletReturn\Hydrators;

use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\Concerns\AsAction;

class PalletReturnHydrateUniversalSearch
{
    use AsAction;


    public function handle(PalletReturn $palletReturn): void
    {
        $palletReturn->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'        => $palletReturn->group_id,
                'organisation_id' => $palletReturn->organisation_id,
                'section'         => 'fulfilment',
                'title'           => $palletReturn->reference,
                'slug'            => 'par-'.$palletReturn->slug,
            ]
        );
    }

}

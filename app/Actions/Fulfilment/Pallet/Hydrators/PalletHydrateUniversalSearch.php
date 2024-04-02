<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Fulfilment\Pallet\Hydrators;

use App\Models\Fulfilment\Pallet;
use Lorisleiva\Actions\Concerns\AsAction;

class PalletHydrateUniversalSearch
{
    use AsAction;


    public function handle(Pallet $pallet): void
    {
        $pallet->universalSearch()->updateOrCreate(
            [],
            [
                'section' => 'fulfilment',
                'title'   => $pallet->reference??$pallet->id,
                'ulid'    => $pallet->slug,
            ]
        );
    }

}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Mar 2024 14:21:35 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Models\Fulfilment\PalletReturn;

class UpdatePalletReturnStateFromItems extends HydrateModel
{
    use WithActionUpdate;

    public function handle(PalletReturn $palletReturn): void
    {
        $palletCount = $palletReturn->pallets()->count();

        $palletPickedCount    = $palletReturn->pallets()->where('state', PalletStateEnum::PICKED)->count();
        $palletNotPickedCount = $palletReturn->pallets()->where('state', PalletStateEnum::NOT_PICKED)->count();


        if (($palletPickedCount + $palletNotPickedCount) == $palletCount) {
            PickedPalletReturn::run($palletReturn);
        }
    }
}

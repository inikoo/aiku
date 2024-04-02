<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Mar 2024 14:21:35 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\PalletReturn\PalletReturnItemStateEnum;
use App\Models\Fulfilment\PalletReturn;

class UpdatePalletReturnStateFromItems extends HydrateModel
{
    use WithActionUpdate;

    public function handle(PalletReturn $palletReturn): void
    {
        $palletCount = $palletReturn->pallets()->count();

        $palletPickedCount    = $palletReturn->pallets()
            ->wherePivot('state', PalletReturnItemStateEnum::PICKED)->count();
        $palletNotPickedCount = $palletReturn->pallets()
            ->wherePivot('state', PalletReturnItemStateEnum::NOT_PICKED)->count();

        if (($palletPickedCount + $palletNotPickedCount) == $palletCount) {
            PickedPalletReturn::run($palletReturn);
        }
    }
}

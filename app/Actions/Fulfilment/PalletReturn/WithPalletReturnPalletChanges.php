<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 23 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Validation\ValidationException;

trait WithPalletReturnPalletChanges
{
    protected function lockPalletReturnForPalletChanges(PalletReturn $palletReturn, bool $onlyWhileInProcess): void
    {
        $state = PalletReturn::query()->whereKey($palletReturn->id)->lockForUpdate()->value('state');

        if ($onlyWhileInProcess && $state !== PalletReturnStateEnum::IN_PROCESS) {
            throw ValidationException::withMessages([
                'message' => __('This return is :state, its pallets can no longer be changed.', ['state' => PalletReturnStateEnum::labels()[$state->value] ?? $state->value]),
            ]);
        }
    }
}

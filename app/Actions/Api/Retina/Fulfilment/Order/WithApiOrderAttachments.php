<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Api\Retina\Fulfilment\Order;

use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Models\Fulfilment\PalletReturn;

trait WithApiOrderAttachments
{
    private PalletReturn $palletReturn;

    public function afterValidator($validator): void
    {
        if (in_array($this->palletReturn->state, [PalletReturnStateEnum::DISPATCHED, PalletReturnStateEnum::CANCEL])) {
            $validator->errors()->add('message', 'This Order is already in the "'.$this->palletReturn->state->value.'" state and its documents cannot be changed.');
        }
    }
}

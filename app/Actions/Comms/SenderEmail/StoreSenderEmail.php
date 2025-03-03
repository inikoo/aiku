<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\SenderEmail;

use App\Enums\Comms\SenderEmail\SenderEmailStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\Comms\SenderEmail;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreSenderEmail
{
    use AsAction;

    public function handle(Shop $shop, array $modelData): SenderEmail
    {
        data_set($modelData, 'state', SenderEmailStateEnum::VERIFICATION_NOT_SUBMITTED);

        return $shop->senderEmail()->create($modelData);
    }
}

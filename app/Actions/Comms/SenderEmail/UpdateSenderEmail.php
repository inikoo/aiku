<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\SenderEmail;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Comms\SenderEmail;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateSenderEmail
{
    use AsAction;
    use WithActionUpdate;

    public function handle(SenderEmail $senderEmail, array $modelData): SenderEmail
    {
        return $this->update($senderEmail, $modelData);
    }
}

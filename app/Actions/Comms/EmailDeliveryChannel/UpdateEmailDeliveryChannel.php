<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 19 Dec 2024 18:08:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailDeliveryChannel;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Comms\EmailDeliveryChannel;

class UpdateEmailDeliveryChannel
{
    use WithActionUpdate;


    public function handle(EmailDeliveryChannel $emailDeliveryChannel, array $modelData): EmailDeliveryChannel
    {
        return $this->update($emailDeliveryChannel, $modelData, ['data']);
    }
}

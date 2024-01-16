<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Nov 2023 17:01:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\MailshotSendChannel;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Mail\MailshotSendChannel;

class UpdateMailshotSendChannel
{
    use WithActionUpdate;


    public function handle(MailshotSendChannel $mailshotSendChannel, array $modelData): MailshotSendChannel
    {

        $mailshotSendChannel = $this->update($mailshotSendChannel, $modelData, ['data']);




        return $mailshotSendChannel;
    }


}

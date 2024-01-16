<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 08:23:57 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\MailshotSendChannel;

use App\Enums\Mail\Mailshot\MailshotStateEnum;
use App\Enums\Mail\MailshotSendChannel\MailshotSendChannelStateEnum;
use App\Models\Mail\Mailshot;
use App\Models\Mail\MailshotSendChannel;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreMailshotSendChannel
{
    use AsAction;

    public function handle(Mailshot $mailshot, array $modelData = []): MailshotSendChannel
    {
        data_set($modelData, 'number_emails', 0, overwrite: false);

        $mailshot->refresh();
        if($mailshot->state==MailshotStateEnum::STOPPED) {
            data_set($modelData, 'state', MailshotSendChannelStateEnum::STOPPED->value);
        }

        /** @var MailshotSendChannel $mailshotSendChannel */
        $mailshotSendChannel = $mailshot->channels()->create($modelData);

        return $mailshotSendChannel;
    }


}

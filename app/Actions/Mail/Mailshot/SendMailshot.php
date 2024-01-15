<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 16:11:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Enums\Mail\Mailshot\MailshotStateEnum;
use App\Models\Mail\Mailshot;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;

class SendMailshot
{
    use AsCommand;
    use AsAction;
    use WithMailshotStateOps;

    public function handle(Mailshot $mailshot, array $modelData): Mailshot
    {
        if (!$mailshot->start_sending_at) {
            data_set($modelData, 'start_sending_at', now());
        }
        data_set($modelData, 'state', MailshotStateEnum::SENDING);

        $mailshot->update($modelData);

        ProcessSendMailshot::dispatch($mailshot);

        return $mailshot;
    }


}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 20 Nov 2023 13:52:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Enums\Mail\Mailshot\MailshotStateEnum;
use App\Enums\Mail\MailshotSendChannel\MailshotSendChannelStateEnum;
use App\Models\Mail\Mailshot;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;

class StopMailshot
{
    use AsCommand;
    use AsAction;
    use WithMailshotStateOps;

    public function handle(Mailshot $mailshot, array $modelData): Mailshot
    {
        if (!$mailshot->start_sending_at or $mailshot->state == MailshotStateEnum::STOPPED) {
            return $mailshot;
        }
        data_set($modelData, 'stopped_at', now());
        data_set($modelData, 'state', MailshotStateEnum::STOPPED);

        $mailshot->update($modelData);

        DB::table('mailshot_send_channels')
            ->where('mailshot_id', $mailshot->id)
            ->whereIn('state', [MailshotSendChannelStateEnum::READY, MailshotSendChannelStateEnum::SENDING])
            ->update(['state' => MailshotSendChannelStateEnum::STOPPED]);

        return $mailshot;
    }



}

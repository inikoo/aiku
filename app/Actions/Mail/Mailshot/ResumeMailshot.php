<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 20 Nov 2023 13:55:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\Mail\MailshotSendChannel\SendMailshotChannel;
use App\Enums\Mail\Mailshot\MailshotStateEnum;
use App\Enums\Mail\MailshotSendChannel\MailshotSendChannelStateEnum;
use App\Models\Mail\Mailshot;
use App\Models\Mail\MailshotSendChannel;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;

class ResumeMailshot
{
    use AsCommand;
    use AsAction;
    use WithMailshotStateOps;

    public function handle(Mailshot $mailshot, array $modelData): Mailshot
    {
        if ($mailshot->state != MailshotStateEnum::STOPPED) {
            return $mailshot;
        }


        data_set($modelData, 'stopped_at', null);
        data_set($modelData, 'state', MailshotStateEnum::SENDING);


        $mailshot->update($modelData);

        DB::table('mailshot_send_channels')
            ->where('mailshot_id', $mailshot->id)
            ->where('state', MailshotSendChannelStateEnum::STOPPED)
            ->update(['state' => MailshotSendChannelStateEnum::READY]);


        foreach(MailshotSendChannel::where('mailshot_id', $mailshot->id)
            ->where('state', MailshotSendChannelStateEnum::READY)->get() as $mailshotSendChannel) {
            SendMailshotChannel::dispatch($mailshotSendChannel);
        }

        return $mailshot;
    }


}

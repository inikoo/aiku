<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Nov 2023 13:34:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Enums\Mail\Mailshot\MailshotStateEnum;
use App\Enums\Mail\MailshotSendChannel\MailshotSendChannelStateEnum;
use App\Models\Mail\Mailshot;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateMailshotSentState
{
    use AsAction;

    public function handle(Mailshot $mailshot): array
    {
        if (!$mailshot->recipients_stored_at) {
            return [
                'msg'=> 'emails still processing'
            ];
        }
        $count = $mailshot->channels()->count();
        if ($count == 0) {
            return [
                'error'=> true,
                'msg'  => 'no channels found'
            ];
        }

        $countInProcess = $mailshot->channels()->whereNot('mailshot_send_channels.state', MailshotSendChannelStateEnum::SENT)->count();

        if ($countInProcess > 0) {
            return [
                'error'=> true,
                'msg'  => 'Channels still processing '.$countInProcess
            ];
        }

        $sentAtDate = $mailshot->channels()->max('sent_at');


        UpdateMailshot::run(
            $mailshot,
            [
            'state'  => MailshotStateEnum::SENT,
            'sent_at'=> $sentAtDate
        ]
        );
        return [
            'msg'=> 'mailshot sent'
        ];
    }

    public string $commandSignature = 'mailshot:sent-state {mailshot}';


    public function asCommand(Command $command): int
    {
        try {
            $mailshot = Mailshot::where('slug', $command->argument('mailshot'))->firstOrFail();
        } catch (Exception) {
            $command->error('Mailshot not found');

            return 1;
        }
        $res=$this->handle($mailshot);

        $command->line(Arr::get($res, 'msg', ''));

        return 0;
    }

}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Nov 2023 17:02:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\MailshotSendChannel;

use App\Actions\Mail\Mailshot\Hydrators\MailshotHydrateCumulativeDispatchedEmailsState;
use App\Actions\Mail\Mailshot\Hydrators\MailshotHydrateDispatchedEmailsState;
use App\Actions\Mail\Mailshot\UpdateMailshotSentState;
use App\Actions\Mail\Mailshot\WithSendMailshot;
use App\Enums\Mail\DispatchedEmail\DispatchedEmailStateEnum;
use App\Enums\Mail\Mailshot\MailshotStateEnum;
use App\Enums\Mail\MailshotSendChannel\MailshotSendChannelStateEnum;
use App\Models\Mail\Mailshot;
use App\Models\Mail\MailshotSendChannel;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SendMailshotChannel
{
    use AsAction;
    use WithSendMailshot;

    public string $jobQueue = 'ses';

    public function handle(MailshotSendChannel $mailshotSendChannel): void
    {
        $mailshot      = $mailshotSendChannel->mailshot;
        $layout        = $mailshot->layout;
        $emailHtmlBody = $layout['html']['html'];


        UpdateMailshotSendChannel::run(
            $mailshotSendChannel,
            [
                'start_sending_at' => now(),
                'state'            => MailshotSendChannelStateEnum::SENDING
            ]
        );


        foreach ($mailshot->recipients()->where('channel', $mailshotSendChannel->id)->get() as $recipient) {
            $mailshot->refresh();
            if ($mailshot->state == MailshotStateEnum::STOPPED) {
                UpdateMailshotSendChannel::run(
                    $mailshotSendChannel,
                    [
                        'state' => MailshotSendChannelStateEnum::STOPPED
                    ]
                );

                return;
            }


            $unsubscribeUrl = route('org.unsubscribe.mailshot.show', $recipient->dispatchedEmail->ulid);


            $this->sendEmailWithMergeTags(
                $recipient->dispatchedEmail,
                $mailshot->sender(),
                $mailshot->subject,
                $emailHtmlBody,
                $unsubscribeUrl,
            );


        }


        UpdateMailshotSendChannel::run(
            $mailshotSendChannel,
            [
                'sent_at' => now(),
                'state'   => MailshotSendChannelStateEnum::SENT
            ]
        );
        $mailshot->refresh();
        MailshotHydrateCumulativeDispatchedEmailsState::run($mailshot, DispatchedEmailStateEnum::SENT);
        MailshotHydrateDispatchedEmailsState::run($mailshot);
        UpdateMailshotSentState::run($mailshot);
    }

    public string $commandSignature = 'mailshot:send-channel {mailshot} {?channel}';


    public function asCommand(Command $command): int
    {
        try {
            $mailshot = Mailshot::where('slug', $command->argument('mailshot'))->firstOrFail();
        } catch (Exception) {
            $command->error('Mailshot not found');

            return 1;
        }

        $chanelQuery = $mailshot->channels();
        if ($command->argument('channel')) {
            $chanelQuery->where('channel.id', $command->argument('channel'));
        }

        foreach ($chanelQuery->get() as $channel) {
            $this->handle($channel);
        }


        return 0;
    }

}

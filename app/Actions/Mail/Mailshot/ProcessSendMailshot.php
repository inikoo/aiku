<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Dec 2023 09:24:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\Mail\DispatchedEmail\StoreDispatchedEmail;
use App\Actions\Mail\Mailshot\Hydrators\MailshotHydrateDispatchedEmailsState;
use App\Actions\Mail\Mailshot\Hydrators\MailshotHydrateEmails;
use App\Actions\Mail\MailshotRecipient\StoreMailshotRecipient;
use App\Actions\Mail\MailshotSendChannel\SendMailshotChannel;
use App\Actions\Mail\MailshotSendChannel\StoreMailshotSendChannel;
use App\Actions\Mail\MailshotSendChannel\UpdateMailshotSendChannel;
use App\Actions\Traits\WithCheckCanContactByEmail;
use App\Helpers\ArrayWIthProbabilities;
use App\Models\Mail\Email;
use App\Models\Mail\Mailshot;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class ProcessSendMailshot
{
    use AsAction;
    use WithCheckCanContactByEmail;

    public string $jobQueue = 'default_long';

    public function tags(): array
    {
        return ['send_mailshot'];
    }

    public function handle(Mailshot $mailshot): void
    {
        $counter      = 0;
        $queryBuilder = GetMailshotRecipientsQueryBuilder::run($mailshot);

        $mailshotSendChannel = StoreMailshotSendChannel::run($mailshot);
        foreach($queryBuilder->get() as $recipient) {


            if($counter>=250) {


                UpdateMailshotSendChannel::run(
                    $mailshotSendChannel,
                    [
                        'number_emails' => $mailshot->recipients()->where('channel', $mailshotSendChannel->id)->count()
                    ]
                );
                SendMailshotChannel::dispatch($mailshotSendChannel);
                $mailshotSendChannel = StoreMailshotSendChannel::run($mailshot);
                $counter             = 0;
            }


            //   if (!$this->canContactByEmail($recipient)) {
            //       continue;
            //   }



            $recipientExists = $mailshot->recipients()->where('recipient_id', $recipient->id)->where('recipient_type', class_basename($recipient))->exists();
            if (!$recipientExists) {
                if (!app()->environment('production') and config('mail.devel.rewrite_mailshot_recipients_email', true)) {
                    $prefixes     = ['success' => 50, 'bounce' => 30, 'complaint' => 20];
                    $prefix       = ArrayWIthProbabilities::make()->getRandomElement($prefixes);
                    $emailAddress = "$prefix+$recipient->slug@simulator.amazonses.com";
                } else {
                    $emailAddress = $recipient->email;
                }

                $email = Email::firstOrCreate(['address' => $emailAddress]);

                $dispatchedEmail = StoreDispatchedEmail::run(
                    email: $email,
                    mailshot: $mailshot,
                    modelData: [
                        'recipient_type' => $recipient->getMorphClass(),
                        'recipient_id'   => $recipient->id

                    ]
                );

                StoreMailshotRecipient::run(
                    $mailshot,
                    $dispatchedEmail,
                    $recipient,
                    [
                        'channel'=> $mailshotSendChannel->id,
                    ]
                );
            }

            $counter++;

        }

        UpdateMailshotSendChannel::run(
            $mailshotSendChannel,
            [
                'number_emails' => $mailshot->recipients()->where('channel', $mailshotSendChannel->id)->count()
            ]
        );
        SendMailshotChannel::dispatch($mailshotSendChannel);



        UpdateMailshot::run(
            $mailshot,
            [
                'recipients_stored_at' => now()
            ]
        );
        MailshotHydrateEmails::run($mailshot);
        MailshotHydrateDispatchedEmailsState::run($mailshot);



    }


    public string $commandSignature = 'mailshot:send {mailshot}';


    public function asCommand(Command $command): int
    {
        try {
            $mailshot = Mailshot::where('slug', $command->argument('mailshot'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }


        $this->handle($mailshot);

        return 0;
    }


}

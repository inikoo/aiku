<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 20 Nov 2023 23:40:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot\Hydrators;

use App\Enums\Mail\DispatchedEmail\DispatchedEmailStateEnum;
use App\Events\MailshotPusherEvent;
use App\Models\Mail\Mailshot;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class MailshotHydrateCumulativeDispatchedEmailsState
{
    use AsAction;

    private Mailshot $mailshot;

    public function __construct(Mailshot $mailshot)
    {
        $this->mailshot = $mailshot;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->mailshot->id))->dontRelease()];
    }


    public function handle(
        Mailshot $mailshot,
        DispatchedEmailStateEnum $state
    ): void {
        if ($state==DispatchedEmailStateEnum::READY) {
            MailshotHydrateEmails::run($mailshot);
            return;
        }

        /** @noinspection PhpUncoveredEnumCasesInspection */
        $count = DB::table('dispatched_emails')
            ->where('mailshot_id', $mailshot->id)->where('is_test', false)
            ->where(
                match ($state) {
                    DispatchedEmailStateEnum::ERROR        => 'is_error',
                    DispatchedEmailStateEnum::REJECTED     => 'is_rejected',
                    DispatchedEmailStateEnum::SENT         => 'is_sent',
                    DispatchedEmailStateEnum::DELIVERED    => 'is_delivered',
                    DispatchedEmailStateEnum::HARD_BOUNCE  => 'is_hard_bounced',
                    DispatchedEmailStateEnum::SOFT_BOUNCE  => 'is_soft_bounced',
                    DispatchedEmailStateEnum::OPENED       => 'is_opened',
                    DispatchedEmailStateEnum::CLICKED      => 'is_clicked',
                    DispatchedEmailStateEnum::SPAM         => 'is_spam',
                    DispatchedEmailStateEnum::UNSUBSCRIBED => 'is_unsubscribed',
                },
                true
            )->count();




        /** @noinspection PhpUncoveredEnumCasesInspection */
        $mailshot->mailshotStats()->update(
            [
                match ($state) {
                    DispatchedEmailStateEnum::ERROR        => 'number_error_emails',
                    DispatchedEmailStateEnum::REJECTED     => 'number_rejected_emails',
                    DispatchedEmailStateEnum::SENT         => 'number_sent_emails',
                    DispatchedEmailStateEnum::DELIVERED    => 'number_delivered_emails',
                    DispatchedEmailStateEnum::HARD_BOUNCE  => 'number_hard_bounced_emails',
                    DispatchedEmailStateEnum::SOFT_BOUNCE  => 'number_soft_bounced_emails',
                    DispatchedEmailStateEnum::OPENED       => 'number_opened_emails',
                    DispatchedEmailStateEnum::CLICKED      => 'number_clicked_emails',
                    DispatchedEmailStateEnum::SPAM         => 'number_spam_emails',
                    DispatchedEmailStateEnum::UNSUBSCRIBED => 'number_unsubscribed_emails',
                }

                => $count
            ]
        );


        MailshotPusherEvent::dispatch($mailshot);

    }
}

<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 05-03-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Comms\Email;

use App\Actions\Comms\DispatchedEmail\StoreDispatchedEmail;
use App\Actions\Comms\Traits\WithSendBulkEmails;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Comms\DispatchedEmail\DispatchedEmailProviderEnum;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Models\Comms\Email;
use App\Models\Comms\Outbox;
use App\Models\CRM\Customer;

class SendNewCustomerToSubcriberEmail extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;
    use WithSendBulkEmails;

    private Email $email;

    public function handle(Customer $customer)
    {
        // /** @var Outbox $outbox */
        $outboxes = $customer->shop->outboxes()->where('code', OutboxCodeEnum::NEW_CUSTOMER->value)->get();

        foreach ($outboxes as $outbox) {
            $subcribeUsers = $outbox->subscribedUsers;
            /** @var OutBoxHasSubscribers $subcribeUser */
            foreach ($subcribeUsers as $subcribeUser) {
                $recipient       = $subcribeUser->user->email ?? $subcribeUser->external_email;
                $dispatchedEmail = StoreDispatchedEmail::run($outbox->emailOngoingRun, $recipient, [
                    'is_test'       => false,
                    'outbox_id'     => $outbox->id,
                    'email_address' => $recipient->email,
                    'provider'      => DispatchedEmailProviderEnum::SES
                ]);
                $dispatchedEmail->refresh();

                $emailHtmlBody = $outbox->emailOngoingRun->email->liveSnapshot->compiled_layout;

                $this->sendEmailWithMergeTags(
                    $dispatchedEmail,
                    $outbox->emailOngoingRun->sender(),
                    $outbox->emailOngoingRun?->email?->subject,
                    $emailHtmlBody,
                    '',
                    additionalData: [
                        'New customer name' => $customer->name,
                        'New customer email' => $customer->email,
                    ]
                );
            }
        }

    }
}

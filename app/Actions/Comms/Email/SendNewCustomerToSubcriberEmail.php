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
use App\Enums\Comms\Outbox\OutboxBuilderEnum;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Models\Comms\Email;
use App\Models\CRM\Customer;
use Illuminate\Support\Arr;

class SendNewCustomerToSubcriberEmail extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;
    use WithSendBulkEmails;

    private Email $email;

    public function handle(Customer $customer)
    {
        // /** @var Outbox $outbox */
        $outbox = $customer->shop->outboxes()->where('code', OutboxCodeEnum::NEW_CUSTOMER->value)->first();

        $subcribeUsers = $outbox->subscribedUsers;
        foreach ($subcribeUsers as $subcribeUser) {
            // $recipient       = $subcribeUser->user->email ?? $subcribeUser->external_email;
            if ($subcribeUser->user) {
                $recipient = $subcribeUser->user;
            } else {
                $recipient = $subcribeUser;
            }
            $dispatchedEmail = StoreDispatchedEmail::run($outbox->emailOngoingRun, $recipient, [
                'is_test'       => false,
                'outbox_id'     => $outbox->id,
                'email_address' => $recipient->email ?? $recipient->external_email,
                'provider'      => DispatchedEmailProviderEnum::SES
            ]);
            $dispatchedEmail->refresh();

            if ($outbox->builder == OutboxBuilderEnum::BLADE) {
                $emailHtmlBody = Arr::get($outbox->emailOngoingRun?->email?->liveSnapshot?->layout, 'blade_template');
            } else {
                $emailHtmlBody = $outbox->emailOngoingRun?->email?->liveSnapshot?->compiled_layout;
            }

            $this->sendEmailWithMergeTags(
                $dispatchedEmail,
                $outbox->emailOngoingRun->sender(),
                $outbox->emailOngoingRun?->email?->subject,
                $emailHtmlBody,
                '',
                additionalData: [
                    'customer_name' => $customer->name
                ]
            );
        }

    }

    // public string $commandSignature = 'xxx';

    // public function asCommand($command){
    //     $c = Customer::first();
    //     // $outbox = $c->shop->outboxes()->where('code', OutboxCodeEnum::NEW_CUSTOMER->value)->first();
    //     // // 'external_email' => 'dev@aw-advantage.com',

    //     // StoreOutboxHasSubscriber::make()->action(
    //     //     Outbox::find(158),
    //     //     [
    //     //         'external_email' => 'artha@aw-advantage.com',
    //     //     ]
    //     // );



    //     $this->handle($c);
    // }



}

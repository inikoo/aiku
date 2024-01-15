<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Nov 2023 02:20:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\MailshotRecipient;

use App\Actions\Mail\Mailshot\Hydrators\MailshotHydrateEmails;
use App\Models\CRM\Customer;
use App\Models\Leads\Prospect;
use App\Models\Mail\DispatchedEmail;
use App\Models\Mail\Mailshot;
use App\Models\Mail\MailshotRecipient;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreMailshotRecipient
{
    use AsAction;

    public function handle(
        Mailshot $mailshot,
        DispatchedEmail $dispatchedEmail,
        Customer|Prospect $recipient,
        array $modelData
    ): MailshotRecipient {
        /** @var MailshotRecipient $mailshotRecipient */
        $mailshotRecipient = $mailshot->recipients()->createOrFirst(
            [
                'recipient_id'   => $recipient->id,
                'recipient_type' => class_basename($recipient),
            ],
            array_merge(
                [
                    'dispatched_email_id' => $dispatchedEmail->id

                ],
                $modelData
            )
        );
        MailshotHydrateEmails::dispatch($mailshot);

        return $mailshotRecipient;
    }


}

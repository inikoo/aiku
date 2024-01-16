<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Nov 2023 00:37:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\DispatchedEmail;

use App\Actions\Mail\Mailshot\Hydrators\MailshotHydrateCumulativeDispatchedEmailsState;
use App\Actions\Mail\Mailshot\Hydrators\MailshotHydrateDispatchedEmailsState;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Mail\DispatchedEmail\DispatchedEmailStateEnum;
use App\Models\Mail\DispatchedEmail;

class UpdateDispatchedEmail
{
    use WithActionUpdate;


    public function handle(DispatchedEmail $dispatchedEmail, array $modelData): DispatchedEmail
    {

        $dispatchedEmail = $this->update($dispatchedEmail, $modelData, ['data']);

        if ($dispatchedEmail->mailshot_id) {
            if ($dispatchedEmail->wasChanged(['is_error'])) {
                MailshotHydrateCumulativeDispatchedEmailsState::run($dispatchedEmail->mailshot, DispatchedEmailStateEnum::ERROR);
            }
            if ($dispatchedEmail->wasChanged(['is_rejected'])) {
                MailshotHydrateCumulativeDispatchedEmailsState::run($dispatchedEmail->mailshot, DispatchedEmailStateEnum::REJECTED);
            }
            if ($dispatchedEmail->wasChanged(['is_sent'])) {
                MailshotHydrateCumulativeDispatchedEmailsState::run($dispatchedEmail->mailshot, DispatchedEmailStateEnum::SENT);
            }
            if ($dispatchedEmail->wasChanged(['is_delivered'])) {
                MailshotHydrateCumulativeDispatchedEmailsState::run($dispatchedEmail->mailshot, DispatchedEmailStateEnum::DELIVERED);
            }
            if ($dispatchedEmail->wasChanged(['is_hard_bounced'])) {
                MailshotHydrateCumulativeDispatchedEmailsState::run($dispatchedEmail->mailshot, DispatchedEmailStateEnum::HARD_BOUNCE);
            }
            if ($dispatchedEmail->wasChanged(['is_soft_bounced'])) {
                MailshotHydrateCumulativeDispatchedEmailsState::run($dispatchedEmail->mailshot, DispatchedEmailStateEnum::SOFT_BOUNCE);
            }
            if ($dispatchedEmail->wasChanged(['is_opened'])) {
                MailshotHydrateCumulativeDispatchedEmailsState::run($dispatchedEmail->mailshot, DispatchedEmailStateEnum::OPENED);
            }
            if ($dispatchedEmail->wasChanged(['is_clicked'])) {
                MailshotHydrateCumulativeDispatchedEmailsState::run($dispatchedEmail->mailshot, DispatchedEmailStateEnum::CLICKED);
            }
            if ($dispatchedEmail->wasChanged(['is_spam'])) {
                MailshotHydrateCumulativeDispatchedEmailsState::run($dispatchedEmail->mailshot, DispatchedEmailStateEnum::SPAM);
            }
            if ($dispatchedEmail->wasChanged(['is_unsubscribed'])) {
                MailshotHydrateCumulativeDispatchedEmailsState::run($dispatchedEmail->mailshot, DispatchedEmailStateEnum::UNSUBSCRIBED);
            }



            if ($dispatchedEmail->wasChanged(['state'])) {
                MailshotHydrateDispatchedEmailsState::dispatch($dispatchedEmail->mailshot);
            }
        }


        return $dispatchedEmail;
    }


}

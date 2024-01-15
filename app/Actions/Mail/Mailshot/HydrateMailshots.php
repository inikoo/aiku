<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 Nov 2023 16:15:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\HydrateModel;
use App\Actions\Mail\Mailshot\Hydrators\MailshotHydrateCumulativeDispatchedEmailsState;
use App\Actions\Mail\Mailshot\Hydrators\MailshotHydrateDispatchedEmailsState;
use App\Actions\Mail\Mailshot\Hydrators\MailshotHydrateEmails;
use App\Actions\Mail\Mailshot\Hydrators\MailshotHydrateEstimatedEmails;
use App\Enums\Mail\DispatchedEmail\DispatchedEmailStateEnum;
use App\Models\Mail\Mailshot;
use Illuminate\Support\Collection;

class HydrateMailshots extends HydrateModel
{
    public function handle(Mailshot $mailshot): void
    {
        MailshotHydrateDispatchedEmailsState::run($mailshot);
        MailshotHydrateEmails::run($mailshot);
        MailshotHydrateCumulativeDispatchedEmailsState::run($mailshot, DispatchedEmailStateEnum::ERROR);
        MailshotHydrateCumulativeDispatchedEmailsState::run($mailshot, DispatchedEmailStateEnum::REJECTED);
        MailshotHydrateCumulativeDispatchedEmailsState::run($mailshot, DispatchedEmailStateEnum::SENT);
        MailshotHydrateCumulativeDispatchedEmailsState::run($mailshot, DispatchedEmailStateEnum::DELIVERED);
        MailshotHydrateCumulativeDispatchedEmailsState::run($mailshot, DispatchedEmailStateEnum::HARD_BOUNCE);
        MailshotHydrateCumulativeDispatchedEmailsState::run($mailshot, DispatchedEmailStateEnum::SOFT_BOUNCE);
        MailshotHydrateCumulativeDispatchedEmailsState::run($mailshot, DispatchedEmailStateEnum::OPENED);
        MailshotHydrateCumulativeDispatchedEmailsState::run($mailshot, DispatchedEmailStateEnum::CLICKED);
        MailshotHydrateCumulativeDispatchedEmailsState::run($mailshot, DispatchedEmailStateEnum::SPAM);
        MailshotHydrateCumulativeDispatchedEmailsState::run($mailshot, DispatchedEmailStateEnum::UNSUBSCRIBED);

        if(!$mailshot->start_sending_at) {
            MailshotHydrateEstimatedEmails::run($mailshot);
        }


    }

    public string $commandSignature = 'hydrate:mailshots {slugs?*}';

    protected function getModel(string $slug): Mailshot
    {
        return Mailshot::firstWhere($slug);
    }

    protected function getAllModels(): Collection
    {
        return Mailshot::get();
    }

}

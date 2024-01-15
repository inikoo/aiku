<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Nov 2023 14:41:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\Mail\Mailshot\Hydrators\MailshotHydrateEstimatedEmails;
use App\Actions\Mail\Outbox\Hydrators\OutboxHydrateMailshots;
use App\Actions\Market\Shop\Hydrators\ShopHydrateMailshots;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateMailshots;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Mail\Mailshot\MailshotTypeEnum;
use App\Models\Mail\Mailshot;
use App\Models\Market\Shop;
use Lorisleiva\Actions\ActionRequest;

class UpdateMailshot
{
    use WithActionUpdate;

    public function handle(Mailshot $mailshot, array $modelData): Mailshot
    {
        $mailshot = $this->update($mailshot, $modelData, ['data']);
        $mailshot->refresh();

        OrganisationHydrateMailshots::dispatch();
        if ($mailshot->type == MailshotTypeEnum::PROSPECT_MAILSHOT) {
            ShopHydrateMailshots::dispatch($mailshot->parent);
        }

        MailshotHydrateEstimatedEmails::run($mailshot);
        OutboxHydrateMailshots::dispatch($mailshot->outbox);

        return $mailshot;
    }


    public function rules(): array
    {
        return [
            'subject'           => ['sometimes', 'string', 'max:255'],
            'recipients_recipe' => ['sometimes', 'array']
        ];
    }

    /**
     * @throws \Exception
     */
    public function shopProspects(Shop $shop, Mailshot $mailshot, ActionRequest $request): Mailshot
    {
        $this->fillFromRequest($request);
        $validatedData = $this->validateAttributes();

        return $this->handle($mailshot, $validatedData);
    }
}

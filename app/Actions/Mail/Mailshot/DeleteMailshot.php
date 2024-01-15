<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Nov 2023 12:29:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\Market\Shop\Hydrators\ShopHydrateMailshots;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateMailshots;
use App\Enums\Mail\Mailshot\MailshotTypeEnum;
use App\Models\Mail\Mailshot;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteMailshot
{
    use AsAction;
    use WithAttributes;

    public bool $isAction = false;

    private Mailshot $mailshot;

    public function handle(Mailshot $mailshot): Mailshot
    {
        $mailshot->delete();

        OrganisationHydrateMailshots::dispatch();
        if ($mailshot->type == MailshotTypeEnum::PROSPECT_MAILSHOT) {
            ShopHydrateMailshots::dispatch($mailshot->parent);
        }

        return $mailshot;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->isAction) {
            return true;
        }

        if ($this->mailshot->type == MailshotTypeEnum::PROSPECT_MAILSHOT) {
            return $request->user()->hasPermissionTo("crm.prospects.edit");
        }


        return false;

    }

    public function action(Mailshot $mailshot): Mailshot
    {
        $this->mailshot=$mailshot;
        return $this->handle($mailshot);
    }

    public function asController(Mailshot $mailshot, ActionRequest $request): Mailshot
    {
        $this->mailshot=$mailshot;
        $request->validate();
        return $this->handle($mailshot);
    }


    public function htmlResponse(Mailshot $mailshot): RedirectResponse
    {
        if ($this->mailshot->type == MailshotTypeEnum::PROSPECT_MAILSHOT) {
            return redirect()->route(
                'org.crm.shop.prospects.mailshots.index',
                [
                    $mailshot->parent->slug,
                    $mailshot->slug
                ]
            );
        }

        return redirect()->route('dashboard');
    }
}

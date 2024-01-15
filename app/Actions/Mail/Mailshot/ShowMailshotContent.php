<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 16:11:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\Traits\WithActionUpdate;
use App\Enums\Mail\Mailshot\MailshotTypeEnum;
use App\Models\Mail\Mailshot;
use Lorisleiva\Actions\ActionRequest;

class ShowMailshotContent
{
    use WithActionUpdate;

    private bool $asAction = false;
    /**
     * @var array|\ArrayAccess|mixed
     */
    public Mailshot $mailshot;

    public function handle(Mailshot $mailshot): array
    {
        return $mailshot->layout;
    }


    public function jsonResponse(array $content): false|string
    {
        return json_encode($content);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $mailshot = $this->mailshot;
        if ($mailshot->type == MailshotTypeEnum::PROSPECT_MAILSHOT) {
            return $request->user()->hasPermissionTo("crm.prospects.edit");
        }

        return false;
    }


    public function asController(Mailshot $mailshot, ActionRequest $request): array
    {
        $this->mailshot = $mailshot;
        $request->validate();

        return $this->handle($mailshot);
    }


}

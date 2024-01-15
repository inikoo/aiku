<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 16:11:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailTemplate\UI;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Mail\EmailTemplate;
use Lorisleiva\Actions\ActionRequest;

class ShowEmailTemplateContent
{
    use WithActionUpdate;

    private bool $asAction = false;
    /**
     * @var array|\ArrayAccess|mixed
     */
    public EmailTemplate $emailTemplate;

    public function handle(EmailTemplate $emailTemplate): array
    {
        return $emailTemplate->compiled;
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

        return $request->user()->hasPermissionTo("crm.prospects.edit");
    }


    public function asController(EmailTemplate $emailTemplate, ActionRequest $request): array
    {
        $request->validate();

        return $this->handle($emailTemplate);
    }
}

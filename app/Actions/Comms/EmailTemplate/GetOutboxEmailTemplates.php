<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailTemplate;

use App\Actions\InertiaAction;
use App\Http\Resources\Mail\EmailTemplateResource;
use App\Models\Comms\Outbox;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsObject;

class GetOutboxEmailTemplates extends InertiaAction
{
    use AsObject;

    public function handle(Outbox $outbox): array
    {
        $selectOptions = [];


        foreach ($outbox->emailTemplates as $template) {
            $selectOptions[$template->id] = EmailTemplateResource::make($template)->getArray();   //new EmailTemplateResource($template);
        }


        return $selectOptions;
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }

    public function asController(Outbox $outbox, ActionRequest $request): array
    {

        return $this->handle($outbox);
    }


}

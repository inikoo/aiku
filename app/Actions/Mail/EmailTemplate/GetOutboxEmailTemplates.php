<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailTemplate;

use App\Actions\InertiaAction;
use App\Http\Resources\Mail\EmailTemplateResource;
use App\Models\Mail\Outbox;
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

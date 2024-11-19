<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailTemplate;

use App\Actions\InertiaAction;
use App\Http\Resources\Mail\EmailTemplateResource;
use App\Models\Comms\EmailTemplate;
use App\Models\Comms\EmailTemplateCategory;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsObject;

class GetSeededEmailTemplates extends InertiaAction
{
    use AsObject;

    public function handle(?string $category = null): array
    {
        $selectOptions = [];

        /** @var EmailTemplate $emailTemplates */
        if ($category == null) {
            $emailTemplates = EmailTemplate::all();
        } else {
            $emailTemplates = EmailTemplateCategory::where('name', $category)->first();
            $emailTemplates = $emailTemplates->templates;
        }

        foreach ($emailTemplates as $template) {
            $selectOptions[$template->id] = EmailTemplateResource::make($template)->getArray();   //new EmailTemplateResource($template);
        }


        return $selectOptions;
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }

    public function asController(ActionRequest $request): array
    {

        return $this->handle($request->get('category'));
    }


}

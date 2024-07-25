<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailTemplate;

use App\Actions\InertiaAction;
use App\Http\Resources\Mail\EmailTemplateResource;
use App\Models\Mail\EmailTemplate;
use App\Models\Mail\EmailTemplateCategory;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsObject;

class GetSeededEmailTemplates extends InertiaAction
{
    use AsObject;

    public function handle(?string $category=null): array
    {
        $selectOptions = [];

        /** @var EmailTemplate $emailTemplates */
        if($category== null) {
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

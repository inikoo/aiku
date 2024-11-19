<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailTemplate;

use App\Actions\OrgAction;
use App\Http\Resources\Mail\EmailTemplateResource;
use App\Models\SysAdmin\Group;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsObject;

class GetSeededEmailTemplates extends OrgAction
{
    use AsObject;

    public function handle(Group $group): array
    {
        $selectOptions = [];

        $emailTemplates = $group->emalTemplates()->where('state', 'seeded')->get();


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

        return $this->handle(group());
    }


}

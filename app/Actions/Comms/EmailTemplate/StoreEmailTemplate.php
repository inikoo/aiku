<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailTemplate;

use App\Actions\OrgAction;
use App\Models\Comms\EmailTemplate;
use App\Models\SysAdmin\Group;

class StoreEmailTemplate extends OrgAction
{
    public function handle(Group $group, array $modelData): EmailTemplate
    {

        /** @var EmailTemplate $emailTemplate */
        $emailTemplate = $group->emailTemplates()->create($modelData);


        return $emailTemplate;
    }

    public function rules(): array
    {
        return [
            'layout'  => ['sometimes', 'array']
        ];
    }


    public function action(Group $group, array $modelData): EmailTemplate
    {
        $this->asAction = true;
        $this->initialisationFromGroup($group, $modelData);

        return $this->handle($group, $this->validatedData);
    }
}

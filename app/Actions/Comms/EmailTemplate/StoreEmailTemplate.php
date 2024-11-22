<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailTemplate;

use App\Actions\OrgAction;
use App\Enums\Comms\EmailTemplate\EmailTemplateProviderEnum;
use App\Enums\Comms\EmailTemplate\EmailTemplateStateEnum;
use App\Models\Comms\EmailTemplate;
use App\Models\Comms\Outbox;
use App\Models\SysAdmin\Group;
use Illuminate\Validation\Rule;

class StoreEmailTemplate extends OrgAction
{
    public function handle(Group|Outbox $parent, array $modelData): EmailTemplate
    {
        /** @var EmailTemplate $emailTemplate */
        if ($parent instanceof Outbox) {
            data_set($modelData, 'group_id', $parent->group_id);
            $emailTemplate = $parent->emailTemplate()->create($modelData);
        } else {
            $emailTemplate = $parent->emailTemplates()->create($modelData);
        }


        return $emailTemplate;
    }

    public function rules(): array
    {
        $rules = [
            'layout'      => ['sometimes', 'array'],
            'name'        => ['required', 'string', 'max:255'],
            'provider'    => ['required', Rule::enum(EmailTemplateProviderEnum::class)],
            'language_id' => ['required', 'exists:languages,id'],
            'data'        => ['sometimes', 'array'],
        ];

        if (!$this->strict) {
            $rules['is_seeded'] = ['required', 'boolean'];
            $rules['state']     = ['required', Rule::enum(EmailTemplateStateEnum::class)];
            $rules['active_at'] = ['sometimes', 'required', 'date'];
        }

        return $rules;
    }


    public function action(Group|Outbox $parent, array $modelData, bool $strict = true): EmailTemplate
    {
        $this->asAction = true;
        $this->strict   = $strict;
        if ($parent instanceof Outbox) {
            $group = $parent->group;
        } else {
            $group = $parent;
        }
        $this->initialisationFromGroup($group, $modelData);

        return $this->handle($parent, $this->validatedData);
    }
}

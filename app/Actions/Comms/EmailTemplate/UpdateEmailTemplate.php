<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailTemplate;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Comms\EmailTemplate\EmailTemplateStateEnum;
use App\Models\Comms\EmailTemplate;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateEmailTemplate extends OrgAction
{
    use WithActionUpdate;

    public function handle(EmailTemplate $emailTemplate, array $modelData): EmailTemplate
    {
        return $this->update($emailTemplate, $modelData, ['data']);
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'name'       => ['sometimes', 'string', 'max:255'],
            'data'       => ['sometimes', 'array'],
            'state'      => ['sometimes', Rule::enum(EmailTemplateStateEnum::class)],
            'active_at'  => ['sometimes', 'date'],
            'suspend_at' => ['sometimes', 'date'],
            'layout'     => ['sometimes', 'array']
        ];
    }

    public function action(EmailTemplate $emailTemplate, $modelData): EmailTemplate
    {
        $this->asAction = true;

        $this->initialisationFromGroup($emailTemplate->group, $modelData);

        return $this->handle($emailTemplate, $this->validatedData);
    }

    public function asController(EmailTemplate $emailTemplate, ActionRequest $request): EmailTemplate
    {
        $this->initialisation($emailTemplate->organisation, $request);

        return $this->action($emailTemplate, $this->validatedData);
    }
}

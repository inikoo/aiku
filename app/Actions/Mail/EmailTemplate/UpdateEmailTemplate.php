<?php

namespace App\Actions\Mail\EmailTemplate;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Mail\EmailTemplate;
use Lorisleiva\Actions\ActionRequest;

class UpdateEmailTemplate extends OrgAction
{
    use WithActionUpdate;

    public function handle(EmailTemplate $emailTemplate, array $modelData): EmailTemplate
    {
        $emailTemplate = $this->update($emailTemplate, $modelData);

        return $emailTemplate;
    }

    public function asController(EmailTemplate $emailTemplate, ActionRequest $request): EmailTemplate
    {
        $this->initialisation($emailTemplate->organisation, $request);

        return $this->action($emailTemplate, $this->validatedData);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => ['sometimes', 'max:255']
        ];
    }

    public function action(EmailTemplate $emailTemplate, $modelData): EmailTemplate
    {
        $this->asAction = true;

        $this->initialisation($emailTemplate->organisation, $modelData);

        return $this->handle($emailTemplate, $this->validatedData);
    }
}

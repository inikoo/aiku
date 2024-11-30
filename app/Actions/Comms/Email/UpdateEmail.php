<?php

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 13:05:43 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Comms\Email;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Comms\Email;
use Lorisleiva\Actions\ActionRequest;

class UpdateEmail extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;

    private Email $email;

    public function handle(Email $email, array $modelData): Email
    {
        $email = $this->update($email, $modelData, ['data']);


        return $email;
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
        $rules = [

        ];

        if (!$this->strict) {
            $rules = $this->noStrictUpdateRules($rules);
        }

        return $rules;
    }

    public function action(Email $email, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): Email
    {
        $this->strict = $strict;
        if (!$audit) {
            Email::disableAuditing();
        }
        $this->asAction       = true;
        $this->email       = $email;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($email->organisation, $modelData);

        return $this->handle($email, $this->validatedData);
    }

    public function asController(Email $email, ActionRequest $request): Email
    {
        $this->email = $email;

        $this->initialisation($email->organisation, $request);

        return $this->handle($email, $this->validatedData);
    }

}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 13 Nov 2024 21:15:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailCopy;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Mail\DispatchedEmail;
use App\Models\Mail\EmailCopy;

class StoreEmailCopy extends OrgAction
{
    use WithNoStrictRules;

    public function handle(DispatchedEmail $dispatchedEmail, array $modelData): EmailCopy
    {
        /** @var EmailCopy $emailCopy */
        return $dispatchedEmail->emailCopy()->create($modelData);
    }

    public function rules(): array
    {
        $rules = [
            'subject' => ['required', 'string'],
            'body'    => ['required', 'string'],
        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    public function action(DispatchedEmail $dispatchedEmail, array $modelData, int $hydratorsDelay = 0, bool $strict = true): EmailCopy
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($dispatchedEmail->organisation, $modelData);

        return $this->handle($dispatchedEmail, $this->validatedData);
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailRun;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Comms\Email\EmailRunStateEnum;
use App\Models\Comms\EmailRun;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateEmailRun extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;


    public function handle(EmailRun $emailRun, array $modelData): EmailRun
    {
        return $this->update($emailRun, $modelData, ['data']);
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
            'subject'           => ['sometimes','required', 'string', 'max:255'],
            'state'             => ['required', Rule::enum(EmailRunStateEnum::class)],
        ];

        if (!$this->strict) {
            $rules = $this->noStrictUpdateRules($rules);
        }

        return $rules;
    }

    public function action(EmailRun $emailRun, array $modelData, int $hydratorsDelay = 0, bool $strict = true): EmailRun
    {
        $this->strict = $strict;
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($emailRun->shop, $modelData);

        return $this->handle($emailRun, $this->validatedData);
    }
    public function asController(EmailRun $emailRun, ActionRequest $request): EmailRun
    {
        $this->initialisationFromShop($emailRun->shop, $request);

        return $this->handle($emailRun, $this->validatedData);
    }



}

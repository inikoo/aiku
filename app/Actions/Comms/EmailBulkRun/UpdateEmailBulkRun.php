<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Nov 2024 13:30:40 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailBulkRun;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Comms\EmailBulkRun\EmailBulkRunStateEnum;
use App\Models\Comms\EmailBulkRun;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateEmailBulkRun extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;


    public function handle(EmailBulkRun $emailRun, array $modelData): EmailBulkRun
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
            'state'             => ['required', Rule::enum(EmailBulkRunStateEnum::class)],
        ];

        if (!$this->strict) {
            $rules = $this->noStrictUpdateRules($rules);
        }

        return $rules;
    }

    public function action(EmailBulkRun $emailRun, array $modelData, int $hydratorsDelay = 0, bool $strict = true): EmailBulkRun
    {
        $this->strict = $strict;
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($emailRun->shop, $modelData);

        return $this->handle($emailRun, $this->validatedData);
    }
    public function asController(EmailBulkRun $emailRun, ActionRequest $request): EmailBulkRun
    {
        $this->initialisationFromShop($emailRun->shop, $request);

        return $this->handle($emailRun, $this->validatedData);
    }



}

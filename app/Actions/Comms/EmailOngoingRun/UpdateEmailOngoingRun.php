<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Nov 2024 08:14:59 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailOngoingRun;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Comms\EmailOngoingRun\EmailOngoingRunStatusEnum;
use App\Models\Comms\EmailOngoingRun;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateEmailOngoingRun extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;


    public function handle(EmailOngoingRun $emailOngoingRun, array $modelData): EmailOngoingRun
    {
        return $this->update($emailOngoingRun, $modelData, ['data']);
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
            'subject'  => ['sometimes', 'required', 'string', 'max:255'],
            'status'   => ['sometimes', 'required', Rule::enum(EmailOngoingRunStatusEnum::class)],
            'email_id' => [
                'sometimes',
                'required',
                Rule::exists('emails', 'id')->where(function ($query) {
                    $query->where('shop_id', $this->shop->id);
                })
            ],
        ];

        if (!$this->strict) {
            $rules['fetched_at'] = ['sometimes', 'nullable', 'date'];
            $rules               = $this->noStrictUpdateRules($rules);
        }

        return $rules;
    }

    public function action(EmailOngoingRun $emailOngoingRun, array $modelData, int $hydratorsDelay = 0, bool $strict = true): EmailOngoingRun
    {
        $this->strict         = $strict;
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($emailOngoingRun->shop, $modelData);

        return $this->handle($emailOngoingRun, $this->validatedData);
    }

    public function asController(EmailOngoingRun $emailOngoingRun, ActionRequest $request): EmailOngoingRun
    {
        $this->initialisationFromShop($emailOngoingRun->shop, $request);

        return $this->handle($emailOngoingRun, $this->validatedData);
    }


}

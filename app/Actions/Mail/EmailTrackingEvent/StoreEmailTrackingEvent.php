<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 19:59:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailTrackingEvent;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Mail\EmailTrackingEvent\EmailTrackingEventTypeEnum;
use App\Models\Mail\DispatchedEmail;
use App\Models\Mail\EmailTrackingEvent;
use Illuminate\Validation\Rule;

class StoreEmailTrackingEvent extends OrgAction
{
    use WithNoStrictRules;

    public function handle(DispatchedEmail $dispatchedEmail, array $modelData): EmailTrackingEvent
    {
        data_set($modelData, 'group_id', $dispatchedEmail->group_id);
        data_set($modelData, 'organisation_id', $dispatchedEmail->organisation_id);

        /** @var EmailTrackingEvent $emailTrackingEvent */
        $emailTrackingEvent = $dispatchedEmail->emailTrackingEvents()->create($modelData);

        return $emailTrackingEvent;
    }

    public function rules(): array
    {
        $rules = [
            'provider_reference' => ['sometimes', 'nullable', 'string', 'max:64'],
            'type'               => ['required', Rule::enum(EmailTrackingEventTypeEnum::class)],
            'data'               => ['sometimes', 'array']
        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    public function action(DispatchedEmail $dispatchedEmail, array $modelData, int $hydratorsDelay = 0, bool $strict = true): EmailTrackingEvent
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($dispatchedEmail->organisation, $modelData);

        return $this->handle($dispatchedEmail, $this->validatedData);
    }
}

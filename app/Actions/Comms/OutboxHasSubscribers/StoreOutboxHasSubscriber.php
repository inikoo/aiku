<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 05-03-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Comms\OutboxHasSubscribers;

use App\Actions\Comms\Outbox\Hydrators\OutboxHydrateSubscribers;
use App\Actions\OrgAction;

use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Comms\Outbox;
use App\Models\Comms\OutBoxHasSubscriber;

use App\Models\SysAdmin\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOutboxHasSubscriber extends OrgAction
{
    use WithNoStrictRules;


    public function handle(Outbox $outbox, array $modelData): OutBoxHasSubscriber
    {
        data_set($modelData, 'group_id', $outbox->group_id);
        data_set($modelData, 'organisation_id', $outbox->organisation_id);

        $outboxHasSubscriber = $outbox->subscribedUsers()->create($modelData);
        OutboxHydrateSubscribers::dispatch($outbox)->delay($this->hydratorsDelay);

        return $outboxHasSubscriber;
    }


    public function rules(): array
    {
        $rules =  [
            'user_id' => [
                'required_if:external_email,null',
                Rule::exists('users', 'id')->where('group_id', $this->group->id),
            ],

            'external_email' => [
                'required_if:user_id,null',
                'email',
            ],
        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }


    public function afterValidator(Validator $validator): void
    {
        if ($this->get('external_email') and $this->get('user_id')) {
            $validator->errors()->add('user_id', 'You can only provide one of user_id or external_email');
        }

        if ($this->get('user_id')) {
            $user=User::find($this->get('user_id'));
            if(!$user->email){
                $validator->errors()->add('user_id', 'User does not have an email address');

            }
        }

    }


    public function action(Outbox $outbox, array $modelData, int $hydratorsDelay = 0, bool $strict = true): OutBoxHasSubscriber
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;

        $this->initialisation($outbox->organisation, $modelData);

        return $this->handle($outbox, $this->validatedData);
    }


}

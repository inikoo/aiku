<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 06-03-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Comms\OutboxHasSubscribers;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Comms\Outbox;
use App\Models\Fulfilment\Fulfilment;
use Lorisleiva\Actions\ActionRequest;

class StoreManyOutboxHasSubscriber extends OrgAction
{
    use WithNoStrictRules;

    public function handle(Outbox $outbox, array $modelData): array
    {
        $externalEmails = $modelData['external_emails'] ?? [];
        $usersId = $modelData['users_id'] ?? [];

        $subscribersData = array_merge(
            array_map(fn ($email) => ['external_email' => $email], $externalEmails),
            array_map(fn ($userId) => ['user_id' => $userId], $usersId)
        );

        foreach ($subscribersData as $data) {
            StoreOutboxHasSubscriber::make()->action($outbox, $data);
        }

        return $outbox->subscribers()->get()->toArray();
    }

    public function rules(): array
    {
        $rules = [
            'users_id' => [
                'array',
            ],

            'external_emails' => [
                'array',
            ],
        ];

        return $rules;
    }

    public function inFulfilment(Fulfilment $fulfilment, Outbox $outbox, ActionRequest $request)
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($outbox, $this->validatedData);
    }
}

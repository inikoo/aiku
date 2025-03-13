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
use App\Http\Resources\Mail\OutboxHasSubscribersResource;
use App\Models\Comms\Outbox;
use App\Models\Fulfilment\Fulfilment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;

class StoreManyOutboxHasSubscriber extends OrgAction
{
    use WithNoStrictRules;

    public function handle(Outbox $outbox, array $modelData): Collection
    {
        $externalEmails = $modelData['external_emails'] ?? [];
        $usersId = $modelData['users_id'] ?? [];

        $subscribersData = array_merge(
            array_map(fn ($email) => ['external_email' => $email], $externalEmails),
            array_map(fn ($userId) => ['user_id' => $userId], $usersId)
        );

        $subscribers = new Collection();

        foreach ($subscribersData as $data) {
            $subscribers->push(OutboxHasSubscribersResource::make(StoreOutboxHasSubscriber::make()->action($outbox, $data)));
        }

        return $subscribers;
    }

    public function jsonResponse(Collection $subscribers): AnonymousResourceCollection
    {
        return OutboxHasSubscribersResource::collection($subscribers);
    }

    public function rules(): array
    {
        $rules = [
            'users_id' => [
                'required_if:external_emails,null',
                'array',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $existingSubscribers = \DB::table('outbox_has_subscribers')
                        ->whereIn('user_id', $value)
                        ->pluck('user_id')
                        ->toArray();

                    if (!empty($existingSubscribers)) {
                        $fail('Some users are already subscribed.');
                    }
                },
            ],

            'external_emails' => [
                'required_if:users_id,null',
                'array',
                function ($attribute, $value, $fail) {
                    $existingEmails = \DB::table('outbox_has_subscribers')
                        ->whereIn('external_email', $value)
                        ->pluck('external_email')
                        ->toArray();

                    if (!empty($existingEmails)) {
                        $fail('Some emails are already subscribed.');
                    }
                },
            ],
        ];

        return $rules;
    }

    public function inFulfilment(Fulfilment $fulfilment, Outbox $outbox, ActionRequest $request)
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        $this->handle($outbox, $this->validatedData);
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\DispatchedEmail;

use App\Actions\Mail\EmailAddress\StoreEmailAddress;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Mail\DispatchedEmail\DispatchedEmailProviderEnum;
use App\Enums\Mail\DispatchedEmail\DispatchedEmailStateEnum;
use App\Enums\Mail\DispatchedEmail\DispatchedEmailTypeEnum;
use App\Enums\Mail\Outbox\OutboxBlueprintEnum;
use App\Models\CRM\Customer;
use App\Models\CRM\Prospect;
use App\Models\Mail\DispatchedEmail;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreDispatchEmail extends OrgAction
{
    use WithNoStrictRules;


    public function handle(Outbox|Mailshot $parent, Customer|Prospect|User $recipient, array $modelData): DispatchedEmail
    {
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'shop_id', $parent->shop_id);

        if (class_basename($parent) == 'Mailshot') {
            $outbox = $parent->outbox;
            data_set($modelData, 'outbox_id', $parent->outbox_id);
        } else {
            $outbox = $parent;
        }


        data_set(
            $modelData,
            'type',
            match ($outbox->blueprint) {
                OutboxBlueprintEnum::MAILSHOT => DispatchedEmailTypeEnum::MARKETING,
                OutboxBlueprintEnum::EMAIL_TEMPLATE => DispatchedEmailTypeEnum::TRANSACTIONAL,
                OutboxBlueprintEnum::TEST => DispatchedEmailTypeEnum::TEST,
                OutboxBlueprintEnum::INVITE => DispatchedEmailTypeEnum::INVITE,
            }
        );


        data_set($modelData, 'recipient_type', class_basename($recipient));
        data_set($modelData, 'recipient_id', $recipient->id);


        $emailAddress = StoreEmailAddress::run($parent->group, Arr::pull($modelData, 'email_address'));
        data_set($modelData, 'email_address_id', $emailAddress->id);


        /** @var DispatchedEmail $dispatchedEmail */
        $dispatchedEmail = $parent->dispatchedEmails()->create($modelData);

        return $dispatchedEmail;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("mail.edit");
    }

    public function rules(): array
    {
        $rules = [
            'email_address'        => ['required', 'email'],
            'provider'             => ['required', Rule::enum(DispatchedEmailProviderEnum::class)],
            'provider_dispatch_id' => ['sometimes', 'required', 'string'],
        ];

        if (!$this->strict) {
            $rules['state']                = ['required', Rule::enum(DispatchedEmailStateEnum::class)];
            $rules['email_address']        = ['required', 'string'];
            $rules['provider_dispatch_id'] = ['sometimes', ' nullable', 'string'];

            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    public function action(Outbox|Mailshot $parent, Customer|Prospect|User $recipient, array $modelData, int $hydratorsDelay = 0, bool $strict = true): DispatchedEmail
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($parent->organisation, $modelData);

        return $this->handle($parent, $recipient, $this->validatedData);
    }
}

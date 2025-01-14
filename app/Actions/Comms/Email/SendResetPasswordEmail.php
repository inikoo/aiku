<?php

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 13:05:43 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Comms\Email;

use App\Actions\Comms\Mailshot\SendMailshotTest;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Comms\Email;
use App\Models\Comms\Outbox;
use App\Models\CRM\Customer;
use Lorisleiva\Actions\ActionRequest;

class SendResetPasswordEmail extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;

    private Email $email;

    public function handle(Customer $customer, array $modelData)
    {
        /** @var Outbox $passwordOutbox */
        $passwordOutbox = $customer->shop->outboxes()->where('code', 'password_reminder')->first();

        return SendMailshotTest::run($passwordOutbox, [
            'emails' => [
                $customer->email
            ]
        ]);
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

    public function action(Customer $customer, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): Email|string
    {
        $this->strict = $strict;
        if (!$audit) {
            Email::disableAuditing();
        }
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($customer->organisation, $modelData);

        return $this->handle($customer, $this->validatedData);
    }

    public function asController(Customer $customer, ActionRequest $request): Email
    {
        $this->initialisation($customer->organisation, $request);

        return $this->handle($customer, $this->validatedData);
    }

}

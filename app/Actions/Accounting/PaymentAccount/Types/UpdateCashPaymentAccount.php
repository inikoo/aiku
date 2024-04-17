<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:19:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount\Types;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Accounting\OrgPaymentServiceProvider;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use Lorisleiva\Actions\ActionRequest;

class UpdateCashPaymentAccount extends OrgAction
{
    use WithActionUpdate;

    public OrgPaymentServiceProvider|PaymentServiceProvider $parent;

    public function handle(PaymentAccount $paymentAccount, array $modelData): PaymentAccount
    {
        return $this->update($paymentAccount, $modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("accounting.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'max:250', 'string']
        ];
    }

    public function action(PaymentAccount $paymentAccount, array $modelData): PaymentAccount
    {
        $this->asAction = true;
        $this->initialisation($paymentAccount->organisation, $modelData);

        return $this->handle($paymentAccount, $this->validatedData);
    }
}

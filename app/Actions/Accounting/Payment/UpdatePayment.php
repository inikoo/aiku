<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 14:31:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Payment;

use App\Actions\Accounting\Payment\Search\PaymentRecordSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Accounting\PaymentsResource;
use App\Models\Accounting\Payment;
use Lorisleiva\Actions\ActionRequest;

class UpdatePayment extends OrgAction
{
    use WithActionUpdate;

    public function handle(Payment $payment, array $modelData): Payment
    {
        $payment = $this->update($payment, $modelData, ['data']);

        PaymentRecordSearch::dispatch($payment);

        return $payment;
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
        $rules = [
            'reference'    => ['sometimes', 'nullable', 'max:255', 'string'],
            'amount'       => ['sometimes', 'decimal:0,2'],
            'org_amount'   => ['sometimes', 'numeric'],
            'grp_amount' => ['sometimes', 'numeric'],
        ];

        if (!$this->strict) {
            $rules['last_fetched_at'] = ['sometimes', 'date'];
        }

        return $rules;
    }

    public function action(Payment $payment, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): Payment
    {
        $this->strict = $strict;
        if (!$audit) {
            Payment::disableAuditing();
        }
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($payment->shop, $modelData);

        return $this->handle($payment, $this->validatedData);
    }


    public function asController(Payment $payment, ActionRequest $request): Payment
    {
        $this->initialisationFromShop($payment->shop, $request);

        return $this->handle($payment, $this->validatedData);
    }

    public function jsonResponse(Payment $payment): PaymentsResource
    {
        return new PaymentsResource($payment);
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:19:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Payment;

use App\Actions\Accounting\Payment\Hydrators\PaymentHydrateUniversalSearch;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Sales\Customer;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StorePayment
{
    use AsAction;
    use WithAttributes;

    private bool $asAction=false;

    public function handle(PaymentAccount|Customer $parent, array $modelData): Payment
    {
        if (class_basename($parent)=='Customer') {
            $modelData['shop_id']=$parent->shop_id;
        }

        /** @var Payment $payment */
        $payment = $parent->payments()->create($modelData);
        PaymentHydrateUniversalSearch::dispatch($payment);
        return $payment;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("accounting.edit");
    }
    public function rules(): array
    {
        return [
            'code' => ['required', 'unique:tenant.payments', 'between:2,9', 'alpha_dash'],
        ];
    }

    public function action(PaymentAccount|Customer $parent, array $objectData): Payment
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $validatedData);
    }
}

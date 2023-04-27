<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:19:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentServiceProvider;

use App\Models\Accounting\PaymentServiceProvider;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StorePaymentServiceProvider
{
    use AsAction;
    use WithAttributes;
    private bool $asAction=false;

    public function handle(array $modelData): PaymentServiceProvider
    {
        /** @var PaymentServiceProvider $paymentServiceProvider */
        $paymentServiceProvider = PaymentServiceProvider::create($modelData);
        $paymentServiceProvider->stats()->create();
        return $paymentServiceProvider;
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
            'code'         => ['required', 'unique:tenant.payment_service_providers', 'between:2,64', 'alpha_dash'],
            'name'         => ['required', 'max:250', 'string'],
        ];
    }

    public function action(array $objectData): PaymentServiceProvider
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($validatedData);
    }
}

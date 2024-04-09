<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 14:28:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentServiceProvider;

use App\Actions\GrpAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Accounting\PaymentServiceProviderResource;
use App\Models\Accounting\PaymentServiceProvider;
use Lorisleiva\Actions\ActionRequest;

class UpdatePaymentServiceProvider extends GrpAction
{
    use WithActionUpdate;

    private PaymentServiceProvider $paymentServiceProvider;

    public function handle(PaymentServiceProvider $paymentServiceProvider, array $modelData): PaymentServiceProvider
    {
        return $this->update($paymentServiceProvider, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("sysadmin.edit");
    }

    public function rules(): array
    {
        return [
            'name'      => [
                'sometimes',
                'required',
                'max:255',
                'string',
            ],
        ];
    }

    public function asController(PaymentServiceProvider $paymentServiceProvider, ActionRequest $request): PaymentServiceProvider
    {
        $this->paymentServiceProvider = $paymentServiceProvider;
        $this->initialisation($paymentServiceProvider->group, $request);

        return $this->handle($paymentServiceProvider, $this->validatedData);
    }

    public function action(PaymentServiceProvider $paymentServiceProvider, $modelData): PaymentServiceProvider
    {
        $this->asAction               = true;
        $this->paymentServiceProvider = $paymentServiceProvider;
        $this->initialisation($paymentServiceProvider->group, $modelData);

        return $this->handle($paymentServiceProvider, $this->validatedData);
    }

    public function jsonResponse(PaymentServiceProvider $paymentServiceProvider): PaymentServiceProviderResource
    {
        return new PaymentServiceProviderResource($paymentServiceProvider);
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:19:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentServiceProvider;

use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderTypeEnum;
use App\Models\Accounting\PaymentServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
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
            'code'         => ['required', 'unique:payment_service_providers', 'between:2,64', 'alpha_dash'],
            'type'         => ['required', Rule::in(PaymentServiceProviderTypeEnum::values())],
        ];
    }

    public function action(array $objectData): PaymentServiceProvider
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($validatedData);
    }

    public function asController(ActionRequest $request): PaymentServiceProvider
    {
        $request->validate();

        return $this->handle($request->validated());
    }

    public function htmlResponse(PaymentServiceProvider $paymentServiceProvider): RedirectResponse
    {
        return Redirect::route('grp.accounting.payment-service-providers.show', $paymentServiceProvider->slug);
    }
}

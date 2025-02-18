<?php

/*
 * author Arya Permana - Kirin
 * created on 24-01-2025-11h-25m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\SysAdmin;

use App\Actions\Fulfilment\FulfilmentCustomer\RegisterFulfilmentCustomer;
use App\Actions\RetinaAction;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class RegisterRetinaFulfilmentCustomer extends RetinaAction
{
    /**
     * @throws \Throwable
     */
    public function handle(Fulfilment $fulfilment, array $modelData): FulfilmentCustomer
    {
        $fulfilmentCustomer = RegisterFulfilmentCustomer::make()->action($fulfilment, $modelData);

        return $fulfilmentCustomer;
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }

    public function htmlResponse()
    {
        return Redirect::route('retina.login.show');
    }

    public function rules(): array
    {
        return [
            'contact_name'             => ['required', 'string', 'max:255'],
            'company_name'             => ['required', 'string', 'max:255'],
            'email'                    => [
                'required',
                'string',
                'max:255',
                'exclude_unless:deleted_at,null',
                new IUnique(
                    table: 'customers',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'phone'                    => ['required', 'max:255'],
            'contact_address'          => ['required', new ValidAddress()],
            'interest'                 => ['required', 'required'],
            'password'                 =>
                [
                    'sometimes',
                    'required',
                    app()->isLocal() || app()->environment('testing') ? null : Password::min(8)
                ],
            'product'                           => ['required', 'string'],
            'shipments_per_week'                 => ['required', 'string'],
            'size_and_weight'                   => ['required', 'string'],

        ];
    }

    /**
     * @throws \Throwable
     */
    public function asController(Fulfilment $fulfilment, ActionRequest $request): FulfilmentCustomer
    {
        $this->registerInitialisation($fulfilment, $request);
        return $this->handle($fulfilment, $this->validatedData);
    }
}

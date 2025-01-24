<?php
/*
 * author Arya Permana - Kirin
 * created on 24-01-2025-08h-57m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\WebUser\StoreWebUser;
use App\Actions\OrgAction;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class RegisterFulfilmentCustomer extends OrgAction
{
    /**
     * @throws \Throwable
     */
    public function handle(Fulfilment $fulfilment, array $modelData): FulfilmentCustomer
    {

        $product = Arr::pull($modelData, 'product');
        $shipment = Arr::pull($modelData, 'shipments_per_week');
        $sizeAndWeight = Arr::pull($modelData, 'size_and_weight');
        $password = Arr::pull($modelData, 'password');

        $customer = StoreCustomer::make()->action($fulfilment->shop, $modelData);


        $webUser = StoreWebUser::make()->action($customer, [
            'contact_name' => Arr::get($modelData, 'contact_name'),
            'username' => Arr::get($modelData, 'email'),
            'email' => Arr::get($modelData, 'email'),
            'password' => $password,
            'is_root'   => true,
        ]);

        data_set($fulfilmmentCustomerModelData, 'pallets_storage', in_array('pallets_storage', $modelData['interest']));
        data_set($fulfilmmentCustomerModelData, 'items_storage', in_array('items_storage', $modelData['interest']));
        data_set($fulfilmmentCustomerModelData, 'dropshipping', in_array('dropshipping', $modelData['interest']));
        data_set($fulfilmmentCustomerModelData, 'product', $product);
        data_set($fulfilmmentCustomerModelData, 'shipments_per_week', $shipment);
        data_set($fulfilmmentCustomerModelData, 'size_and_weight', $sizeAndWeight);

        $fulfilmentCustomer = UpdateFulfilmentCustomer::run($customer->fulfilmentCustomer, $fulfilmmentCustomerModelData);

        return $fulfilmentCustomer;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return true;
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
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment, $this->validatedData);
    }

    /**
     * @throws \Throwable
     */
    public function action(Fulfilment $fulfilment, array $modelData): FulfilmentCustomer
    {
        $this->asAction = true;
        $this->initialisationFromFulfilment($fulfilment, $modelData);

        return $this->handle($fulfilment, $this->validatedData);
    }

}

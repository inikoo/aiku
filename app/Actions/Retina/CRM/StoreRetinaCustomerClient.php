<?php
/*
 * author Arya Permana - Kirin
 * created on 17-01-2025-09h-26m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\CRM;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateClients;
use App\Actions\Dropshipping\CustomerClient\Search\CustomerClientRecordSearch;
use App\Actions\OrgAction;
use App\Actions\RetinaAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithModelAddressActions;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Dropshipping\CustomerClient;
use App\Rules\IUnique;
use App\Rules\Phone;
use App\Rules\ValidAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;

class StoreRetinaCustomerClient extends RetinaAction
{
    use WithModelAddressActions;
    use WithNoStrictRules;

    protected Customer $customer;
    /**
     * @throws \Throwable
     */
    public function handle(Customer $customer, array $modelData): CustomerClient
    {
        $address = Arr::get($modelData, 'address');
        Arr::forget($modelData, 'address');

        data_set($modelData, 'ulid', Str::ulid());
        data_set($modelData, 'group_id', $customer->group_id);
        data_set($modelData, 'organisation_id', $customer->organisation_id);
        data_set($modelData, 'shop_id', $customer->shop_id);


        $customerClient = DB::transaction(function () use ($customer, $modelData, $address) {
            /** @var CustomerClient $customerClient */
            $customerClient = $customer->clients()->create($modelData);
            $customerClient->stats()->create();

            return $this->addAddressToModelFromArray(
                model: $customerClient,
                addressData: $address,
                scope: 'delivery',
                canShip: true
            );
        });


        CustomerClientRecordSearch::dispatch($customerClient)->delay($this->hydratorsDelay);
        CustomerHydrateClients::dispatch($customer)->delay($this->hydratorsDelay);

        return $customerClient;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            return true;
        }


        return false;
    }

    public function rules(): array
    {
        $rules = [

            'reference'      => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                new IUnique(
                    table: 'customer_clients',
                    extraConditions: [
                        ['column' => 'customer_id', 'value' => $this->customer->id],
                    ]
                ),
            ],
            'contact_name'   => ['nullable', 'string', 'max:255'],
            'company_name'   => ['nullable', 'string', 'max:255'],
            'email'          => ['nullable', 'email'],
            'phone'          => ['nullable', new Phone()],
            'address'        => ['required', new ValidAddress()],
            'deactivated_at' => ['sometimes', 'nullable', 'date'],
            'status'         => ['sometimes', 'boolean'],

        ];

        if (!$this->strict) {
            $rules          = $this->noStrictStoreRules($rules);
            $rules['email'] = ['sometimes', 'nullable', 'string', 'max:255'];
            $rules['phone'] = ['sometimes', 'nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::route('retina.dropshipping.client.index');
    }

    /**
     * @throws \Throwable
     */
    public function asController(ActionRequest $request): CustomerClient
    {
        $customer       = $request->user()->customer;
        $this->customer = $customer;
        $this->initialisation($request);

        return $this->handle($customer, $this->validatedData);
    }


}

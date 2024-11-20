<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\Helpers\TaxCategory\GetTaxCategory;
use App\Actions\Ordering\Order\Search\OrderRecordSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithOrderingAmountNoStrictFields;
use App\Actions\Traits\WithFixedAddressActions;
use App\Actions\Traits\WithModelAddressActions;
use App\Actions\Traits\WithOrderExchanges;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Enums\Ordering\Order\OrderHandingTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Order\OrderStatusEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Ordering\Order;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\Response;

class StoreOrder extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithFixedAddressActions;
    use WithModelAddressActions;
    use HasOrderHydrators;
    use WithOrderExchanges;
    use WithOrderingAmountNoStrictFields;

    public int $hydratorsDelay = 0;

    private CustomerClient|Customer $parent;

    /**
     * @throws \Throwable
     */
    public function handle(Shop|Customer|CustomerClient $parent, array $modelData): Order
    {
        if (!Arr::get($modelData, 'reference')) {
            data_set(
                $modelData,
                'reference',
                GetSerialReference::run(
                    container: $parent->shop,
                    modelType: SerialReferenceModelEnum::ORDER
                )
            );
        }
        data_set($modelData, 'date', now());

        $billingAddress  = Arr::pull($modelData, 'billing_address');
        $deliveryAddress = Arr::pull($modelData, 'delivery_address');

        if (!$billingAddress && !$deliveryAddress) {
            if ($parent instanceof Customer) {
                $billingAddress  = $parent->address;
                $deliveryAddress = $parent->deliveryAddress;
            } elseif ($parent instanceof CustomerClient) {
                $billingAddress  = $parent->customer->address;
                $deliveryAddress = $parent->address;
            }
        }

        if (class_basename($parent) == 'Customer') {
            $modelData['customer_id'] = $parent->id;
            $modelData['currency_id'] = $parent->shop->currency_id;
            $modelData['shop_id']     = $parent->shop_id;
        } elseif (class_basename($parent) == 'CustomerClient') {
            $modelData['customer_id']        = $parent->customer_id;
            $modelData['customer_client_id'] = $parent->id;
            $modelData['currency_id']        = $parent->shop->currency_id;
            $modelData['shop_id']            = $parent->shop_id;
        } else {
            $modelData['currency_id'] = $parent->currency_id;
            $modelData['shop_id']     = $parent->id;
        }

        if (!Arr::exists($modelData, 'tax_category_id')) {
            if ($parent instanceof Shop) {
                $taxNumber = null;
            } elseif ($parent instanceof Customer) {
                $taxNumber = $parent->taxNumber;
            } else {
                $taxNumber = $parent->customer->taxNumber;
            }
            data_set(
                $modelData,
                'tax_category_id',
                GetTaxCategory::run(
                    country: $this->organisation->country,
                    taxNumber: $taxNumber,
                    billingAddress: $billingAddress,
                    deliveryAddress: $deliveryAddress
                )->id
            );
        }


        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);

        $modelData = $this->processExchanges($modelData, $parent->shop);

        $order = DB::transaction(function () use ($parent, $modelData, $billingAddress, $deliveryAddress) {
            /** @var Order $order */
            $order = Order::create($modelData);
            $order->refresh();
            $order->stats()->create();

            if ($order->billing_locked) {
                $order = $this->createFixedAddress(
                    $order,
                    $billingAddress,
                    'Ordering',
                    'billing',
                    'billing_address_id'
                );
            } else {
                $order = $this->addAddressToModel(
                    model: $order,
                    addressData: Arr::except($billingAddress->toArray(), ['id']),
                    scope: 'billing',
                    updateLocation: false,
                    updateAddressField: 'billing_address_id'
                );
            }

            $order->updateQuietly(
                [
                    'billing_country_id' => $order->billingAddress->country_id
                ]
            );


            if ($order->handing_type == OrderHandingTypeEnum::SHIPPING) {
                if ($order->delivery_locked) {
                    $order = $this->createFixedAddress(
                        $order,
                        $deliveryAddress,
                        'Ordering',
                        'delivery',
                        'delivery_address_id'
                    );
                } else {
                    $order = $this->addAddressToModel(
                        model: $order,
                        addressData: Arr::except($deliveryAddress->toArray(), ['id']),
                        scope: 'delivery',
                        updateLocation: false,
                        updateAddressField: 'delivery_address_id'
                    );
                }

                $order->updateQuietly(
                    [
                        'delivery_country_id' => $order->deliveryAddress->country_id
                    ]
                );
            } else {
                $order->updateQuietly(
                    [
                        'collection_address_id' => $order->shop->collection_address_id,
                        'delivery_country_id'   => $order->shop->collectionAddress->country_id
                    ]
                );
            }

            return $order;
        });

        $this->orderHydrators($order);

        OrderRecordSearch::dispatch($order);

        return $order->fresh();
    }

    public function rules(): array
    {
        $rules = [
            'reference'          => [
                'sometimes',
                'max:64',
                'string',
                new IUnique(
                    table: 'orders',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                    ]
                ),
            ],
            'date'               => ['sometimes', 'date'],
            'submitted_at'       => ['sometimes', 'nullable', 'date'],
            'in_warehouse_at'    => ['sometimes', 'nullable', 'date'],
            'packed_at'          => ['sometimes', 'nullable', 'date'],
            'finalised_at'       => ['sometimes', 'nullable', 'date'],
            'dispatched_at'      => ['sometimes', 'nullable', 'date'],
            'customer_reference' => ['sometimes', 'string', 'max:64'],
            'state'              => ['sometimes', Rule::enum(OrderStateEnum::class)],
            'status'             => ['sometimes', Rule::enum(OrderStatusEnum::class)],
            'handing_type'       => ['sometimes', 'required', Rule::enum(OrderHandingTypeEnum::class)],
            'billing_address'    => ['sometimes', new ValidAddress()],
            'delivery_address'   => ['sometimes', new ValidAddress()],
            'billing_locked'     => ['sometimes', 'boolean'],
            'delivery_locked'    => ['sometimes', 'boolean'],
            'tax_category_id'    => ['sometimes', 'required', 'exists:tax_categories,id'],
            'sales_channel_id'   => [
                'sometimes',
                'required',
                Rule::exists('sales_channels', 'id')->where(function ($query) {
                    $query->where('group_id', $this->shop->group_id);
                })
            ],

        ];

        if (!$this->strict) {
            $rules['reference']    = ['sometimes', 'string', 'max:64'];
            $rules['source_id']    = ['sometimes', 'string', 'max:64'];
            $rules['fetched_at']   = ['sometimes', 'required', 'date'];
            $rules['created_at']   = ['sometimes', 'required', 'date'];
            $rules['cancelled_at'] = ['sometimes', 'nullable', 'date'];
            $rules                 = $this->mergeOrderingAmountNoStrictFields($rules);
        }

        return $rules;
    }

    public function prepareForValidation(): void
    {
        if ($this->get('handing_type') == OrderHandingTypeEnum::COLLECTION and !$this->shop->collection_address_id) {
            abort(400, 'Collection orders require a collection address');
        }

        if ($this->get('handing_type') == OrderHandingTypeEnum::COLLECTION and !$this->shop->collectionAddress->country_id) {
            abort(400, 'Invalid collection address');
        }
    }

    public function htmlResponse(Order $order, ActionRequest $request): Response
    {
        $routeName = $request->route()->getName();

        return match ($routeName) {
            'grp.models.customer.order.store' => Inertia::location(route('grp.org.shops.show.crm.customers.show.orders.show', [
                'organisation' => $order->organisation->slug,
                'shop'         => $order->shop->slug,
                'customer'     => $order->customer->slug,
                'order'        => $order->slug
            ])),
            'grp.models.customer-client.order.store' => Inertia::location(route('grp.org.shops.show.crm.customers.show.customer-clients.orders.show', [
                'organisation'   => $order->organisation->slug,
                'shop'           => $order->shop->slug,
                'customer'       => $order->customer->slug,
                'customerClient' => $order->customerClient->ulid,
                'order'          => $order->slug
            ])),
        };
    }

    /**
     * @throws \Throwable
     */
    public function action(Shop|Customer|CustomerClient $parent, array $modelData, bool $strict = true, int $hydratorsDelay = 60, $audit = true): Order
    {
        if (!$audit) {
            Order::disableAuditing();
        }

        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = $strict;


        $shop = match (class_basename($parent)) {
            'Shop' => $parent,
            'Customer', 'CustomerClient' => $parent->shop,
        };

        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

    /**
     * @throws \Throwable
     */
    public function inCustomer(Customer $customer, ActionRequest $request): Order
    {
        $this->parent = $customer;
        $this->initialisationFromShop($customer->shop, $request);

        return $this->handle($customer, $this->validatedData);
    }

    /**
     * @throws \Throwable
     */
    public function inCustomerClient(CustomerClient $customerClient, ActionRequest $request): Order
    {
        $this->parent = $customerClient;
        $this->initialisationFromShop($customerClient->shop, $request);

        return $this->handle($customerClient, $this->validatedData);
    }


}

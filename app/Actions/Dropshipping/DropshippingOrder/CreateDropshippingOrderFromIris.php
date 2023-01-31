<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 14 Dec 2022 18:27:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\DropshippingOrder;

use App\Actions\fromIris;
use App\Actions\SourceFetch\Aurora\FetchOrders;
use App\Http\Resources\Sales\DropshippingOrderResource;
use App\Managers\Tenant\SourceTenantManager;
use App\Models\Helpers\Address;
use App\Models\Marketing\Product;
use App\Models\Sales\Order;
use App\Models\Web\WebUser;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;


class CreateDropshippingOrderFromIris extends fromIris
{

    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            [
                'order_number'                  => ['required', 'alpha_dash', 'max:255'],
                'email'                         => ['sometimes', 'email'],
                'company_name'                  => ['sometimes', 'string'],
                'contact_name'                  => ['sometimes', 'string'],
                'client_reference'              => ['required', 'string', 'max:255'],
                'phone'                         => ['sometimes', 'string'],
                'delivery_address'              => ['required', 'array:address_line_1,address_line_2,sorting_code,postal_code,locality,dependant_locality,administrative_area,country_code'],
                'delivery_address.country_code' => ['required', 'string', 'size:2', 'exists:countries,code'],
                'items'                         => ['required', 'array'],
                'environment'                   => App::environment()


            ]

        );
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function handle(WebUser $webUser, array $modelData): ?Order
    {
        if ($webUser->customer->orders()->where('customer_number', Arr::get($modelData, 'order_number'))->exists()) {
            throw  ValidationException::withMessages([
                                                         'code' => 'Order number already exists'
                                                     ]);
        }


        $deliveryAddress = new Address([
                                           'address_line_1'      => Arr::get($modelData, 'delivery_address.address_line_1'),
                                           'address_line_2'      => Arr::get($modelData, 'delivery_address.address_line_2'),
                                           'sorting_code'        => Arr::get($modelData, 'delivery_address.sorting_code'),
                                           'postal_code'         => Arr::get($modelData, 'delivery_address.postal_code'),
                                           'locality'            => Arr::get($modelData, 'delivery_address.locality'),
                                           'dependant_locality'  => Arr::get($modelData, 'delivery_address.dependant_locality'),
                                           'administrative_area' => Arr::get($modelData, 'delivery_address.administrative_area'),
                                           'country_code'        => Arr::get($modelData, 'delivery_address.country_code')
                                       ]);


        $items = [];

        foreach (Arr::get($modelData, 'items') as $productID => $item) {
            $product = Product::find($productID);

            if (!$product) {
                throw  ValidationException::withMessages([
                                                             'items' => 'Product id=$productID  not found'
                                                         ]);
            }
            if ($product->shop_id != $webUser->customer->shop_id) {
                throw  ValidationException::withMessages([
                                                             'items' => 'Product id=$productID not found in store'
                                                         ]);
            }


            if ($product->owner_type == 'Customer' and $product->owner_id != $webUser->customer_id) {
                throw  ValidationException::withMessages([
                                                             'items' => 'Product id=$productID not found'
                                                         ]);
            }


            $items[] = [
                'item_type' => 'HistoricProduct',
                'item_id'   => $product->current_historic_product_id,
                'quantity'  => Arr::get($item, 'quantity'),
                'source_id' => $product->source_id
            ];
        }


        // aurora  ========


        $auroraOrderData = [
            'customer_key' => $webUser->customer->source_id,
            'client'       => [
                'code' => Arr::get($modelData, 'client_reference'),
            ],
            'order'        => [
                'order_number'     => Arr::get($modelData, 'order_number'),
                'company_name'     => Arr::get($modelData, 'company_name'),
                'contact_name'     => Arr::get($modelData, 'contact_name'),
                'email'            => Arr::get($modelData, 'email'),
                'phone'            => Arr::get($modelData, 'phone'),
                'delivery_address' => $deliveryAddress->toArray()
            ],
            'items'        => $items
        ];

        if (!Arr::get(tenant(), 'source.db_name')) {
            throw new Exception('Aurora DB not set');
        }

        $database_settings = data_get(config('database.connections'), 'aurora');

        data_set($database_settings, 'database', Arr::get(tenant(), 'source.db_name'));
        config(['database.connections.aurora' => $database_settings]);
        DB::connection('aurora');
        DB::purge('aurora');

        $id = DB::connection('aurora')->table('pika_api_orders')->insertGetId(
            [
                'data'       => json_encode($auroraOrderData),
                'created_at' => Carbon::now()
            ]
        );



        $response = Http::get(Arr::get(tenant(), 'source.url').'/pika/process_pika_order.php', [
            'id'          => $id,
            'environment' => App::environment()
        ]);

        if ($response->ok()) {
            $auroraResponse = $response->json();
            if (Arr::get($auroraResponse, 'status') == 'ok') {
                $tenantSource = app(SourceTenantManager::class)->make(Arr::get(tenant()->source, 'type'));
                $tenantSource->initialisation(tenant());

                $order = FetchOrders::run($tenantSource, Arr::get($auroraResponse, 'order_source_id'));
                if (!$order) {
                    throw new Exception('Error not fetching order');
                }

                return $order;
            }
            throw new Exception('Aurora server error msg:'.Arr::get($auroraResponse, 'msg'));
        } else {
            $exceptionData=[
                Arr::get(tenant(), 'source.url').'/pika/process_pika_order.php',
                [
                    'id'          => $id,
                    'environment' => App::environment()
                ]
            ];
            throw new Exception('Aurora server error: '.json_encode($exceptionData));
        }
    }

    public function jsonResponse(?Order $order): DropshippingOrderResource
    {
        if (!$order) {
            abort(500, 'Could not create order');
        }

        return new DropshippingOrderResource($order);
    }


}


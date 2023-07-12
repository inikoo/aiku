<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer;

use App\Actions\Auth\WebUser\DeleteWebUser;
use App\Actions\Dropshipping\CustomerClient\DeleteCustomerClient;
use App\Actions\Fulfilment\FulfilmentOrder\DeleteFulfilmentOrder;
use App\Actions\Inventory\Stock\DeleteStock;
use App\Actions\Market\Product\DeleteProduct;
use App\Actions\Market\Shop\Hydrators\ShopHydrateCustomers;
use App\Actions\OMS\Order\DeleteOrder;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithTenantArgument;
use App\Models\CRM\Customer;
use Illuminate\Console\Command;

class DeleteCustomer
{
    use WithActionUpdate;
    use WithTenantArgument;

    public string $commandSignature = 'delete:customer {tenant} {id}';

    protected array $deletedDependants;

    public function __construct()
    {
        $this->deletedDependants = [
            'clients'          => 0,
            'webUsers'         => 0,
            'products'         => 0,
            'fulfilmentOrders' => 0,
            'orders'           => 0,
        ];
    }

    public function handle(Customer $customer, array $deletedData = [], bool $skipHydrate = false): Customer
    {
        $this->deletedDependants = [
            'clients'          => $customer->clients()->count(),
            'webUsers'         => $customer->webUsers()->count(),
            'products'         => $customer->products()->count(),
            'fulfilmentOrders' => $customer->fulfilmentOrders()->count(),
            'orders'           => $customer->orders()->count(),
        ];

        $dependantDeletedData = [
            'data' => [
                'deleted' => ['cause' => 'deleted_customer']
            ]
        ];
        foreach ($customer->clients as $client) {
            DeleteCustomerClient::run(
                customerClient: $client,
                skipHydrate:    true,
                deletedData:    $dependantDeletedData
            );
        }

        foreach ($customer->webUsers as $webUser) {
            DeleteWebUser::run(
                webUser:     $webUser,
                skipHydrate: true,
                deletedData: $dependantDeletedData
            );
        }

        foreach ($customer->orders as $order) {
            DeleteOrder::run(
                order:       $order,
                skipHydrate: true,
                deletedData: $dependantDeletedData
            );
        }

        foreach ($customer->fulfilmentOrders as $fulfilmentOrder) {
            DeleteFulfilmentOrder::run(
                fulfilmentOrder: $fulfilmentOrder,
                skipHydrate:     true,
                deletedData:     $dependantDeletedData
            );
        }

        foreach ($customer->products as $product) {
            DeleteProduct::run(
                product:     $product,
                skipHydrate: true,
                deletedData: $dependantDeletedData
            );
        }

        foreach ($customer->stocks as $stock) {
            DeleteStock::run(
                stock:       $stock,
                skipHydrate: true,
                deletedData: $dependantDeletedData
            );
        }


        $customer->delete();
        $customer = $this->update($customer, $deletedData, ['data']);

        if (!$skipHydrate) {
            ShopHydrateCustomers::dispatch($customer->shop);
        }

        return $customer;
    }


    public function asCommand(Command $command): int
    {
        $this->getTenant($command)->execute(
            function () use ($command) {
                $customer = Customer::findOrFail($command->argument('id'));
                $customer = $this->handle($customer);

                print_r($this->deletedDependants);

                // $this->table(
                //     ['Name', 'Email'],
                //     User::all(['name', 'email'])->toArray()
                // );
            }
        );



        return 0;
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Feb 2023 22:05:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer;

use App\Actions\Dropshipping\CustomerClient\DeleteCustomerClient;
use App\Actions\Fulfilment\FulfilmentOrder\CancelFulfilmentOrder;
use App\Actions\Inventory\Stock\DeleteStock;
use App\Actions\Marketing\Product\DeleteProduct;
use App\Actions\Sales\Order\CancelOrder;
use App\Actions\Web\WebUser\DeleteWebUser;
use App\Models\Sales\Customer;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;


class DeleteCustomer
{
    use AsAction;

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

    public function handle(Customer $customer): bool
    {
        $this->deletedDependants = [
            'clients'          => $customer->clients()->count(),
            'webUsers'         => $customer->webUsers()->count(),
            'products'         => $customer->products()->count(),
            'fulfilmentOrders' => $customer->fulfilmentOrders()->count(),
            'orders'           => $customer->orders()->count(),
        ];

        $deletedData = [
            'data' => [
                'deleted' => ['cause' => 'deleted_customer']
            ]
        ];
        foreach ($customer->clients as $client) {
            DeleteCustomerClient::run(
                customerClient: $client,
                skipHydrate:    true,
                deletedData:    $deletedData
            );
        }

        foreach ($customer->webUsers as $webUser) {
            DeleteWebUser::run(
                webUser:     $webUser,
                skipHydrate: true,
                deletedData: $deletedData
            );
        }

        foreach ($customer->orders as $order) {
            CancelOrder::run(
                order:       $order,
                skipHydrate: true,
                deletedData:  $deletedData
            );
        }

        foreach ($customer->fulfilmentOrders as $fulfilmentOrder) {
            CancelFulfilmentOrder::run(
                fulfilmentOrder: $fulfilmentOrder,
                skipHydrate:     true,
                deletedData:      $deletedData
            );
        }

        foreach ($customer->products as $product) {
            DeleteProduct::run(
                product:    $product,
                skipHydrate: true,
                deletedData:  $deletedData
            );
        }

        foreach ($customer->stocks as $stock) {
            DeleteStock::run(
                stock:       $stock,
                skipHydrate: true,
                deletedData:  $deletedData
            );
        }


        return $customer->delete();
    }

    /**
     * @throws \Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById
     */
    public function asCommand(Command $command): int
    {
        $tenant = tenancy()->query()->where('code', $command->argument('tenant'))->first();
        tenancy()->initialize($tenant);

        $customer = Customer::findOrFail($command->argument('id'));
        $customer = $this->handle($customer);

        print_r($this->deletedDependants);

        // $this->table(
        //     ['Name', 'Email'],
        //     User::all(['name', 'email'])->toArray()
        // );

        return 0;
    }

}

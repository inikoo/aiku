<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 22 Apr 2023 09:57:38 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Product\StoreProduct;
use App\Actions\Catalogue\ProductCategory\StoreProductCategory;
use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\WebUser\StoreWebUser;
use App\Actions\Goods\TradeUnit\StoreTradeUnit;
use App\Actions\Helpers\Avatars\GetDiceBearAvatar;
use App\Actions\Inventory\Warehouse\StoreWarehouse;
use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Ordering\Order\StoreOrder;
use App\Actions\Ordering\Transaction\StoreTransaction;
use App\Actions\SysAdmin\Group\StoreGroup;
use App\Actions\SysAdmin\Guest\StoreGuest;
use App\Actions\SysAdmin\Organisation\StoreOrganisation;
use App\Actions\Web\Website\StoreWebsite;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Address;
use App\Models\Inventory\Warehouse;
use App\Models\Catalogue\Shop;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Illuminate\Foundation\Testing\TestCase;

uses(TestCase::class)->in('Feature');
uses(TestCase::class)->group('integration')->in('Integration');

function loadDB(): void
{
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../', '.env.testing');
    $dotenv->load();

    shell_exec(
        './devops/devel/reset_test_database.sh '.
        env('DB_DATABASE_TEST', 'aiku_testing').' '.
        env('DB_PORT').' '.
        env('DB_USERNAME').' '.
        env('DB_PASSWORD').' '.
        env('DB_HOST').
        ' tests/datasets/db_dumps/aiku.dump'
    );
}

function createGroup(): Group
{
    $group = Group::first();
    if (!$group) {
        $group = StoreGroup::make()->action(Group::factory()->definition());
    }
    return $group;
}

function createOrganisation(): Organisation
{
    GetDiceBearAvatar::mock()
        ->shouldReceive('handle')
        ->andReturn(Storage::disk('art')->get('icons/shapes.svg'));

    $group = createGroup();

    $organisation = Organisation::first();
    if (!$organisation) {
        $modelData = Organisation::factory()->definition();
        data_set($modelData, 'code', 'acme');
        data_set($modelData, 'type', OrganisationTypeEnum::SHOP);

        $organisation = StoreOrganisation::make()->action($group, $modelData);
    }

    return $organisation;
}


function createAdminGuest(Group $group): Guest
{
    $guest = Guest::first();
    if (!$guest) {
        app()->instance('group', $group);
        setPermissionsTeamId($group->id);
        $guest = StoreGuest::make()
            ->action(
                $group,
                array_merge(
                    Guest::factory()->definition(),
                    [
                        'roles' => ['super-admin']
                    ]
                )
            );
    }

    return $guest;
}

function createShop(): array
{
    $organisation = createOrganisation();
    $adminGuest   = createAdminGuest($organisation->group);

    $shop = Shop::first();
    if (!$shop) {
        $shop = StoreShop::run(
            $organisation,
            Shop::factory()->definition()
        );
        $shop->refresh();
    }


    return [
        $organisation,
        $adminGuest->user,
        $shop
    ];
}

function createFulfilment(Organisation $organisation): Fulfilment
{
    $group = $organisation->group;
    app()->instance('group', $group);
    setPermissionsTeamId($group->id);
    $organisation = createOrganisation();


    $fulfilment = Fulfilment::first();
    if (!$fulfilment) {
        $shop       = StoreShop::run(
            $organisation,
            array_merge(
                Shop::factory()->definition(),
                [
                    'type'       => ShopTypeEnum::FULFILMENT->value,
                    'warehouses' => [createWarehouse()->id]
                ]
            )
        );
        $fulfilment = $shop->fulfilment;
    }


    return $fulfilment;
}


function createWarehouse(): Warehouse
{
    $organisation = createOrganisation();


    $warehouse = Warehouse::first();
    if (!$warehouse) {
        $warehouse = StoreWarehouse::run(
            $organisation,
            Warehouse::factory()->definition()
        );
        $warehouse->refresh();
    }


    return $warehouse;
}


function createCustomer(Shop $shop): Customer
{
    $customer = $shop->customers()->first();
    if (!$customer) {
        $customer = StoreCustomer::make()->action(
            $shop,
            Customer::factory()->definition(),
        );
    }

    return $customer;
}

function createTradeUnits(Group $group): array
{
    $numberTradeUnits=$group->tradeUnits()->count();
    if($numberTradeUnits<2) {
        $tradeUnit = StoreTradeUnit::make()->action(
            $group,
            TradeUnit::factory()->definition()
        );
        $tradeUnit2=StoreTradeUnit::make()->action(
            $group,
            TradeUnit::factory()->definition()
        );
    } else {
        $tradeUnit =$group->tradeUnits()->first();
        $tradeUnit2=$group->tradeUnits()->skip(1)->first();
    }

    return [
        $tradeUnit,
        $tradeUnit2
    ];

}


function createProduct(Shop $shop): array
{

    $tradeUnits=createTradeUnits($shop->group);

    $department = $shop->productCategories()->where('type', ProductCategoryTypeEnum::DEPARTMENT)->first();
    if (!$department) {
        $departmentData = ProductCategory::factory()->definition();
        data_set($departmentData, 'type', ProductCategoryTypeEnum::DEPARTMENT->value);
        $department = StoreProductCategory::make()->action(
            $shop,
            $departmentData
        );
    }

    $family = $shop->productCategories()->where('type', ProductCategoryTypeEnum::FAMILY)->first();
    if (!$family) {
        $familyData = ProductCategory::factory()->definition();
        data_set($familyData, 'type', ProductCategoryTypeEnum::FAMILY->value);
        $family = StoreProductCategory::make()->action(
            $department,
            $familyData
        );
    }



    $product = $shop->products()->first();
    if (!$product) {
        $productData = array_merge(
            Product::factory()->definition(),
            [
                'trade_units' => [
                    $tradeUnits[0]->id => ['units' => 1]
                ],
                'price'       => 100,
            ]
        );
        $product     = StoreProduct::make()->action(
            $family,
            $productData
        );
    }

    return [
        $tradeUnits,
        $product
    ];
}

function createOrder(Customer $customer, Product $product): Order
{
    $order = $customer->organisation->orders()->first();
    if (!$order) {
        $arrayData = [
            'number'           => '123456',
            'date'             => date('Y-m-d'),
            'customer_id'      => $customer->id,
            'delivery_address' => new Address(Address::factory()->definition()),
            'billing_address'  => new Address(Address::factory()->definition()),
        ];

        $order = StoreOrder::make()->action($customer, $arrayData);

        $transactionData = Transaction::factory()->definition();
        $item            = $product->historicAsset;
        StoreTransaction::make()->action($order, $item, $transactionData);
    }

    return $order;
}

function createWebsite(Shop $shop): Website
{

    if($website=$shop->website) {
        return $website;
    }

    return StoreWebsite::make()->action(
        $shop,
        Website::factory()->definition()
    );

}

function createWebUser(Customer $customer): WebUser
{

    $webUser = $customer->webUsers()->first();
    if (!$webUser) {
        data_set($storeData, 'username', 'test');
        data_set($storeData, 'email', 'test@testmail.com');
        data_set($storeData, 'password', 'test');

        $webUser = StoreWebUser::make()->action(
            $customer,
            $storeData
        );
    }

    return $webUser;

}

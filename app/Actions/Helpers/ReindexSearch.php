<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 00:01:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers;

use App\Actions\Accounting\Invoice\Search\ReindexInvoiceSearch;
use App\Actions\Accounting\TopUp\Search\ReindexTopUpSearch;
use App\Actions\Catalogue\Product\Search\ReindexProductSearch;
use App\Actions\Catalogue\ProductCategory\Search\ReindexProductCategorySearch;
use App\Actions\Catalogue\Service\Search\ReindexServiceSearch;
use App\Actions\CRM\Customer\Search\ReindexCustomerSearch;
use App\Actions\CRM\Prospect\Search\ReindexProspectSearch;
use App\Actions\Dispatching\DeliveryNote\Search\ReindexDeliveryNotesSearch;
use App\Actions\Dropshipping\CustomerClient\Search\ReindexCustomerClientSearch;
use App\Actions\Fulfilment\FulfilmentCustomer\Search\ReindexFulfilmentCustomerSearch;
use App\Actions\Fulfilment\Pallet\Search\ReindexPalletSearch;
use App\Actions\Fulfilment\PalletDelivery\Search\ReindexPalletDeliverySearch;
use App\Actions\Fulfilment\PalletReturn\Search\ReindexPalletReturnSearch;
use App\Actions\Fulfilment\RecurringBill\Search\ReindexRecurringBillSearch;
use App\Actions\Fulfilment\Rental\Search\ReindexRentalSearch;
use App\Actions\Fulfilment\StoredItem\Search\ReindexStoredItem;
use App\Actions\Fulfilment\StoredItemAudit\Search\ReindexStoredItemAuditSearch;
use App\Actions\HumanResources\ClockingMachine\Search\ReindexClockingMachineSearch;
use App\Actions\HumanResources\Employee\Search\ReindexEmployeeSearch;
use App\Actions\HumanResources\JobPosition\Search\ReindexJobPositionSearch;
use App\Actions\HumanResources\Workplace\Search\ReindexWorkplaceSearch;
use App\Actions\HydrateModel;
use App\Actions\Inventory\Location\Search\ReindexLocationSearch;
use App\Actions\Inventory\OrgStock\Search\ReindexOrgStockSearch;
use App\Actions\Inventory\OrgStockFamily\Search\ReindexOrgStockFamilySearch;
use App\Actions\Inventory\Warehouse\Search\ReindexWarehouseSearch;
use App\Actions\Inventory\WarehouseArea\Search\ReindexWarehouseAreaSearch;
use App\Actions\Ordering\Order\Search\ReindexOrdersSearch;
use App\Actions\SysAdmin\User\Search\ReindexUserSearch;
use App\Actions\Traits\WithOrganisationsArgument;
use App\Actions\Web\Website\Search\ReindexWebsiteSearch;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\TopUp;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Service;
use App\Models\CRM\Customer;
use App\Models\CRM\Prospect;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\Rental;
use App\Models\Fulfilment\StoredItem;
use App\Models\Fulfilment\StoredItemAudit;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use App\Models\HumanResources\Workplace;
use App\Models\Inventory\Location;
use App\Models\Inventory\OrgStock;
use App\Models\Inventory\OrgStockFamily;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\Ordering\Order;
use App\Models\SysAdmin\User;
use App\Models\Web\Website;
use Illuminate\Console\Command;

class ReindexSearch extends HydrateModel
{
    use WithOrganisationsArgument;


    public function handle(): void
    {
        $this->reindexFulfilment();
        $this->reindexAccounting();
        $this->reindexHumanResources();
        $this->reindexSysadmin();
        $this->reindexWarehouse();
        $this->reindexWeb();
        $this->reindexCrm();
        $this->reindexCatalogue();
        $this->reindexOrdering();
        $this->reindexDispatching();
    }

    public function reindexFulfilment(): void
    {
        foreach (RecurringBill::withTrashed()->get() as $model) {
            ReindexRecurringBillSearch::run($model);
        }

        foreach (FulfilmentCustomer::withTrashed()->get() as $model) {
            ReindexFulfilmentCustomerSearch::run($model);
        }

        foreach (PalletDelivery::withTrashed()->get() as $model) {
            ReindexPalletDeliverySearch::run($model);
        }

        foreach (PalletReturn::withTrashed()->get() as $model) {
            ReindexPalletReturnSearch::run($model);
        }

        foreach (StoredItemAudit::get() as $model) {
            ReindexStoredItemAuditSearch::run($model);
        }

        foreach (Pallet::withTrashed()->get() as $model) {
            ReindexPalletSearch::run($model);
        }

        foreach (StoredItem::get() as $model) {
            ReindexStoredItem::run($model);
        }

        foreach (Rental::withTrashed()->get() as $model) {
            ReindexRentalSearch::run($model);
        }
    }

    public function reindexAccounting(): void
    {
        foreach (Invoice::withTrashed()->get() as $model) {
            ReindexInvoiceSearch::run($model);
        }
        foreach (TopUp::all() as $model) {
            ReindexTopUpSearch::run($model);
        }
    }

    public function reindexHumanResources(): void
    {
        foreach (Employee::withTrashed()->get() as $model) {
            ReindexEmployeeSearch::run($model);
        }
        foreach (ClockingMachine::withTrashed()->get() as $model) {
            ReindexClockingMachineSearch::run($model);
        }
        foreach (Workplace::withTrashed()->get() as $model) {
            ReindexWorkplaceSearch::run($model);
        }
        foreach (JobPosition::withTrashed()->get() as $model) {
            ReindexJobPositionSearch::run($model);
        }
    }

    public function reindexSysAdmin(): void
    {
        foreach (User::withTrashed()->get() as $model) {
            ReindexUserSearch::run($model);
        }
    }

    public function reindexWarehouse(): void
    {
        foreach (Warehouse::withTrashed()->get() as $model) {
            ReindexWarehouseSearch::run($model);
        }
        foreach (WarehouseArea::withTrashed()->get() as $model) {
            ReindexWarehouseAreaSearch::run($model);
        }
        foreach (Location::withTrashed()->get() as $model) {
            ReindexLocationSearch::run($model);
        }
    }

    public function reindexInventory(): void
    {
        foreach (OrgStockFamily::withTrashed()->get() as $model) {
            ReindexOrgStockFamilySearch::run($model);
        }
        foreach (OrgStock::withTrashed()->get() as $model) {
            ReindexOrgStockSearch::run($model);
        }
    }

    public function reindexWeb(): void
    {
        foreach (Website::withTrashed()->get() as $model) {
            ReindexWebsiteSearch::run($model);
        }
    }

    public function reindexCrm(): void
    {
        foreach (Customer::withTrashed()->get() as $model) {
            ReindexCustomerSearch::run($model);
        }
        foreach (Prospect::withTrashed()->get() as $model) {
            ReindexProspectSearch::run($model);
        }
        foreach (CustomerClient::withTrashed()->get() as $model) {
            ReindexCustomerClientSearch::run($model);
        }
    }

    public function reindexCatalogue(): void
    {
        foreach (ProductCategory::withTrashed()->get() as $model) {
            ReindexProductCategorySearch::run($model);
        }
        foreach (Product::withTrashed()->get() as $model) {
            ReindexProductSearch::run($model);
        }
        foreach (Service::withTrashed()->get() as $model) {
            ReindexServiceSearch::run($model);
        }
    }

    public function reindexOrdering(): void
    {
        foreach (Order::withTrashed()->get() as $model) {
            ReindexOrdersSearch::run($model);
        }
    }

    public function reindexDispatching(): void
    {
        foreach (DeliveryNote::withTrashed()->get() as $model) {
            ReindexDeliveryNotesSearch::run($model);
        }
    }

    public string $commandSignature = 'search:reindex';

    public function asCommand(Command $command): int
    {
        $this->handle();

        $command->line('Guests');
        $command->call('guests:search');

        $command->line('Workplaces');
        $command->call('workplace:search');


        $command->line('Product categories');
        $command->call('product_category:search');

        $command->line('Customers');
        $command->call('customer:search');

        $command->line('Orders');
        $command->call('order:search');


        $command->line('Stock');
        $command->call('stocks:search');

        $command->line('Stock Family');
        $command->call('stock-families:search');

        $command->line('Supplier Asset');
        $command->call('supplier-products:search');

        $command->line('Agent');
        $command->call('agents:search');

        $command->line('Supplier');
        $command->call('suppliers:search');

        $command->line('Webpage');
        $command->call('webpage:search');

        $command->line('Website');
        $command->call('website:search');

        return 0;
    }

}

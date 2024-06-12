<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 18 Aug 2022 23:16:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Facades\Actions;

/**
 * @method forPage(mixed $page, mixed $perPage)
 * @method count()
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {

        $this->app->bind('app.scope', function () {
            return 'aiku';
        });

        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }


    public function boot(): void
    {
        Validator::extend('iunique', function ($attribute, $value, $parameters, $validator) {
            if (isset($parameters[1])) {
                [$connection]  = $validator->parseTable($parameters[0]);
                $wrapped       = DB::connection($connection)->getQueryGrammar()->wrap($parameters[1]);
                $parameters[1] = DB::raw("lower($wrapped)");
            }

            return $validator->validateUnique($attribute, Str::lower($value), $parameters);
        }, trans('validation.iunique'));


        if ($this->app->runningInConsole()) {
            Actions::registerCommands();
        }


        Relation::morphMap(
            [
                // Accounting
                'Invoice'                           => 'App\Models\Accounting\Invoice',
                'InvoiceTransaction'                => 'App\Models\Accounting\InvoiceTransaction',
                'OrgPaymentServiceProvider'         => 'App\Models\Accounting\OrgPaymentServiceProvider',
                'OrgPaymentServiceProviderShop'     => 'App\Models\Accounting\OrgPaymentServiceProviderShop',
                'Payment'                           => 'App\Models\Accounting\Payment',
                'PaymentAccount'                    => 'App\Models\Accounting\PaymentAccount',
                'PaymentAccountShop'                => 'App\Models\Accounting\PaymentAccountShop',
                'PaymentServiceProvider'            => 'App\Models\Accounting\PaymentServiceProvider',

                // Assets
                'Country'      => 'App\Models\Helpers\Country',
                'Currency'     => 'App\Models\Helpers\Currency',
                'Language'     => 'App\Models\Helpers\Language',
                'TariffCode'   => 'App\Models\Helpers\TariffCode',
                'Timezone'     => 'App\Models\Helpers\Timezone',

                // CRM
                'Customer'                  => 'App\Models\CRM\Customer',
                'Prospect'                  => 'App\Models\CRM\Prospect',
                'Appointment'               => 'App\Models\CRM\Appointment',
                'WebUser'                   => 'App\Models\CRM\WebUser',

                // Dispatching
                'DeliveryNote'              => 'App\Models\Dispatch\DeliveryNote',
                'DeliveryNoteItem'          => 'App\Models\Dispatch\DeliveryNoteItem',
                'Event'                     => 'App\Models\Dispatch\Event',
                'PdfLabel'                  => 'App\Models\Dispatch\PdfLabel',
                'Picking'                   => 'App\Models\Dispatch\Picking',
                'Shipment'                  => 'App\Models\Dispatch\Shipment',
                'Shipper'                   => 'App\Models\Dispatch\Shipper',

                // Dropshipping
                'CustomerClient'         => 'App\Models\Dropshipping\CustomerClient',

                // Fulfilment
                'FulfilmentCustomer'               => 'App\Models\Fulfilment\FulfilmentCustomer',
                'Pallet'                           => 'App\Models\Fulfilment\Pallet',
                'PalletDelivery'                   => 'App\Models\Fulfilment\PalletDelivery',
                'PalletReturn'                     => 'App\Models\Fulfilment\PalletReturn',
                'StoredItem'                       => 'App\Models\Fulfilment\StoredItem',
                'Fulfilment'                       => 'App\Models\Fulfilment\Fulfilment',
                'StoredItemMovement'               => 'App\Models\Fulfilment\StoredItemMovement',
                'StoredItemReturn'                 => 'App\Models\Fulfilment\StoredItemReturn',
                'PalletStoredItem'                 => 'App\Models\Fulfilment\PalletStoredItem',
                'PalletDeliveryPallet'             => 'App\Models\Fulfilment\PalletDeliveryPallet',
                'MovementPallet'                   => 'App\Models\Fulfilment\MovementPallet',

                // Goods
                'TradeUnit'              => 'App\Models\Goods\TradeUnit',

                // Helpers
                'Address'          => 'App\Models\Helpers\Address',
                'Audit'            => 'App\Models\Helpers\Audit',
                'Barcode'          => 'App\Models\Helpers\Barcode',
                'CurrencyExchange' => 'App\Models\Helpers\CurrencyExchange',
                'Deployment'       => 'App\Models\Helpers\Deployment',
                'Fetch'            => 'App\Models\Helpers\Fetch',
                'FetchRecord'      => 'App\Models\Helpers\FetchRecord',
                'Issue'            => 'App\Models\Helpers\Issue',
                'Query'            => 'App\Models\Helpers\Query',
                'SerialReference'  => 'App\Models\Helpers\SerialReference',
                'Snapshot'         => 'App\Models\Helpers\Snapshot',
                'Tag'              => 'App\Models\Helpers\Tag',
                'TaxNumber'        => 'App\Models\Helpers\TaxNumber',
                'Upload'           => 'App\Models\Helpers\Upload',
                'UploadRecord'     => 'App\Models\Helpers\UploadRecord',

                // Human Resources
                'JobPosition'                      => 'App\Models\HumanResources\JobPosition',
                'Employee'                         => 'App\Models\HumanResources\Employee',
                'Workplace'                        => 'App\Models\HumanResources\Workplace',
                'Clocking'                         => 'App\Models\HumanResources\Clocking',
                'ClockingMachine'                  => 'App\Models\HumanResources\ClockingMachine',
                'EmployeeJobPosition'              => 'App\Models\HumanResources\EmployeeJobPosition',
                'TimeTracker'                      => 'App\Models\HumanResources\TimeTracker',

                // Inventory
                'Warehouse'                 => 'App\Models\Inventory\Warehouse',
                'WarehouseArea'             => 'App\Models\Inventory\WarehouseArea',
                'Location'                  => 'App\Models\Inventory\Location',
                'OrgStock'                  => 'App\Models\Inventory\OrgStock',
                'LocationOrgStock'          => 'App\Models\Inventory\LocationOrgStock',
                'LostAndFoundStock'         => 'App\Models\Inventory\LostAndFoundStock',
                'StockMovement'             => 'App\Models\Inventory\StockMovement',
                'OrgStockFamily'            => 'App\Models\Inventory\OrgStockFamily',

                // Mail
                'DispatchedEmail'                        => 'App\Models\Mail\DispatchedEmail',
                'Email'                                  => 'App\Models\Mail\Email',
                'EmailAddress'                           => 'App\Models\Mail\EmailAddress',
                'EmailTemplate'                          => 'App\Models\Mail\EmailTemplate',
                'EmailTemplateCategory'                  => 'App\Models\Mail\EmailTemplateCategory',
                'EmailTrackingEvent'                     => 'App\Models\Mail\EmailTrackingEvent',
                'PostRoom'                               => 'App\Models\Mail\PostRoom',
                'Mailshot'                               => 'App\Models\Mail\Mailshot',
                'Outbox'                                 => 'App\Models\Mail\Outbox',
                'SenderEmail'                            => 'App\Models\Mail\SenderEmail',
                'SesNotification'                        => 'App\Models\Mail\SesNotification',

                // Catalogue
                'Shop'                                 => 'App\Models\Catalogue\Shop',
                'ProductCategory'                      => 'App\Models\Catalogue\ProductCategory',
                'Asset'                                => 'App\Models\Catalogue\Asset',
                'HistoricAsset'                        => 'App\Models\Catalogue\HistoricAsset',
                'ShippingZone'                         => 'App\Models\Ordering\ShippingZone',
                'ShippingZoneSchema'                   => 'App\Models\Ordering\ShippingZoneSchema',
                'Product'                              => 'App\Models\Catalogue\Product',
                'Rental'                               => 'App\Models\Fulfilment\Rental',
                'Service'                              => 'App\Models\Catalogue\Service',

                // Deals
                'Offer'                           => 'App\Models\Marketing\Offer',
                'OfferCampaign'                   => 'App\Models\Marketing\OfferCampaign',
                'OfferComponent'                  => 'App\Models\Marketing\OfferComponent',

                // Studio
                'Studio'                  => 'App\Models\Media\Media',

                // Notifications
                'FcmToken'                  => 'App\Models\Notifications\FcmToken',

                // Ordering
                'Order'                        => 'App\Models\Ordering\Order',
                'Transaction'                  => 'App\Models\Ordering\Transaction',

                // Procurement
                'HistoricSupplierProduct'                   => 'App\Models\Procurement\HistoricSupplierProduct',
                'OrgAgent'                                  => 'App\Models\Procurement\OrgAgent',
                'OrgSupplier'                               => 'App\Models\Procurement\OrgSupplier',
                'OrgSupplierProduct'                        => 'App\Models\Procurement\OrgSupplierProduct',
                'PurchaseOrder'                             => 'App\Models\Procurement\PurchaseOrder',
                'PurchaseOrderItem'                         => 'App\Models\Procurement\PurchaseOrderItem',
                'StockDelivery'                             => 'App\Models\Procurement\StockDelivery',
                'StockDeliveryItem'                         => 'App\Models\Procurement\StockDeliveryItem',
                'SupplierProductTradeUnit'                  => 'App\Models\Procurement\SupplierProductTradeUnit',

                // Search
                'UniversalSearch'                  => 'App\Models\Helpers\UniversalSearch',

                // Supply Chain
                'Stock'                  => 'App\Models\SupplyChain\Stock',
                'StockFamily'            => 'App\Models\SupplyChain\StockFamily',
                'SupplierProduct'        => 'App\Models\SupplyChain\SupplierProduct',
                'Supplier'               => 'App\Models\SupplyChain\Supplier',
                'Agent'                  => 'App\Models\SupplyChain\Agent',

                // Sysadmin
                'Admin'                                  => 'App\Models\SysAdmin\Admin',
                'Group'                                  => 'App\Models\SysAdmin\Group',
                'Guest'                                  => 'App\Models\SysAdmin\Guest',
                'Organisation'                           => 'App\Models\SysAdmin\Organisation',
                'OrganisationAuthorisedModels'           => 'App\Models\SysAdmin\OrganisationAuthorisedModels',
                'User'                                   => 'App\Models\SysAdmin\User',
                'Permission'                             => 'App\Models\SysAdmin\Permission',
                'Role'                                   => 'App\Models\SysAdmin\Role',

                // Web
                'Website'                     => 'App\Models\Web\Website',
                'Webpage'                     => 'App\Models\Web\Webpage',
                'ContentBlock'                => 'App\Models\Web\ContentBlock',
                'WebBlock'                    => 'App\Models\Web\WebBlock',
                'WebBlockType'                => 'App\Models\Web\WebBlockType',

                //Manufacturing
                'Production'                  => 'App\Models\Manufacturing\Production',
                'RawMaterial'                 => 'App\Models\Manufacturing\RawMaterial',
                'ManufactureTask'             => 'App\Models\Manufacturing\ManufactureTask',
                'Artefact'                    => 'App\Models\Manufacturing\Artefact'
            ]
        );
    }
}

<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Jul 2024 13:50:35 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Accounting\PaymentAccount\Json\GetShopPaymentAccounts;
use App\Actions\Catalogue\Collection\Json\GetCollections;
use App\Actions\Catalogue\Product\Json\GetOrderProducts;
use App\Actions\Catalogue\Product\Json\GetProducts;
use App\Actions\Catalogue\ProductCategory\Json\GetDepartments;
use App\Actions\Catalogue\ProductCategory\Json\GetFamilies;
use App\Actions\Comms\EmailTemplate\GetEmailTemplateCompiledLayout;
use App\Actions\Comms\EmailTemplate\GetOutboxEmailTemplates;
use App\Actions\Comms\EmailTemplate\GetSeededEmailTemplates;
use App\Actions\Comms\Mailshot\GetMailshotMergeTags;
use App\Actions\Comms\OutboxHasSubscribers\Json\GetOutboxUsers;
use App\Actions\Dispatching\Picking\Packer\Json\GetPackers;
use App\Actions\Dispatching\Picking\Picker\Json\GetPickers;
use App\Actions\Fulfilment\Pallet\Json\GetFulfilmentCustomerStoringPallets;
use App\Actions\Fulfilment\PalletDelivery\Json\GetFulfilmentPhysicalGoods;
use App\Actions\Fulfilment\PalletDelivery\Json\GetFulfilmentServices;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexRecentPalletDeliveryUploads;
use App\Actions\Fulfilment\PalletReturn\Json\GetPalletsInReturnPalletWholePallets;
use App\Actions\Fulfilment\StoredItem\Json\GetPalletAuditStoredItems;
use App\Actions\Helpers\Tag\GetTagOptions;
use App\Actions\Procurement\OrgSupplierProducts\Json\GetOrgSupplierProducts;
use App\Actions\Web\Website\GetWebsiteCloudflareUniqueVisitors;
use Illuminate\Support\Facades\Route;

Route::get('fulfilment/{fulfilment}/comms/outboxes/{outbox}/users', [GetOutboxUsers::class, 'inFulfilment'])->name('fulfilment.outbox.users.index');

Route::get('fulfilment/{fulfilment}/delivery/{scope}/services', [GetFulfilmentServices::class, 'inPalletDelivery'])->name('fulfilment.delivery.services.index');
Route::get('fulfilment/{fulfilment}/return/{scope}/services', [GetFulfilmentServices::class, 'inPalletReturn'])->name('fulfilment.return.services.index');
Route::get('fulfilment/{fulfilment}/recurring-bill/{scope}/services', [GetFulfilmentServices::class, 'inRecurringBill'])->name('fulfilment.recurring-bill.services.index');
Route::get('fulfilment/{fulfilment}/invoice/{scope}/services', [GetFulfilmentServices::class, 'inInvoice'])->name('fulfilment.invoice.services.index');

Route::get('fulfilment/{fulfilment}/delivery/{scope}/physical-goods', [GetFulfilmentPhysicalGoods::class, 'inPalletDelivery'])->name('fulfilment.delivery.physical-goods.index');
Route::get('fulfilment/{fulfilment}/return/{scope}/physical-goods', [GetFulfilmentPhysicalGoods::class, 'inPalletReturn'])->name('fulfilment.return.physical-goods.index');
Route::get('fulfilment/{fulfilment}/recurring-bill/{scope}/physical-goods', [GetFulfilmentPhysicalGoods::class, 'inRecurringBill'])->name('fulfilment.recurring-bill.physical-goods.index');
Route::get('fulfilment/{fulfilment}/invoice/{scope}/physical-goods', [GetFulfilmentPhysicalGoods::class, 'inInvoice'])->name('fulfilment.invoice.physical-goods.index');

Route::get('pallet-return/{palletReturn}/pallets', GetPalletsInReturnPalletWholePallets::class)->name('pallet-return.pallets.index');

Route::get('fulfilment-customer/{fulfilmentCustomer}/storing-pallets', GetFulfilmentCustomerStoringPallets::class)->name('fulfilment-customer.storing-pallets.index');
Route::get('fulfilment-customer/{fulfilmentCustomer}/audit/{storedItemAudit}/stored-items', GetPalletAuditStoredItems::class)->name('fulfilment-customer.audit.stored-items.index');

Route::get('tags', GetTagOptions::class)->name('tags');

Route::get('email/templates/seeded', GetSeededEmailTemplates::class)->name('email_templates.seeded');
Route::get('email/templates/outboxes/{outbox:id}', GetOutboxEmailTemplates::class)->name('email_templates.outbox');
Route::get('email/templates/{emailTemplate:id}/compiled_layout', GetEmailTemplateCompiledLayout::class)->name('email_templates.show.compiled_layout');
Route::get('/mailshot/{mailshot:id}/merge-tags', GetMailshotMergeTags::class)->name('mailshot.merge-tags');

Route::get('shop/{shop}/payment-accounts', GetShopPaymentAccounts::class)->name('shop.payment-accounts');


Route::get('shop/{shop}/catalogue/collection/{scope}/products', GetProducts::class)->name('shop.catalogue.collection.products');
Route::get('shop/{shop}/catalogue/order/{order}/products', GetOrderProducts::class)->name('shop.catalogue.order.products');
Route::get('shop/{shop}/catalogue/{scope}/departments', GetDepartments::class)->name('shop.catalogue.departments');
Route::get('shop/{shop}/catalogue/{scope}/families', GetFamilies::class)->name('shop.catalogue.families');
Route::get('shop/{shop}/catalogue/{scope}/collections', GetCollections::class)->name('shop.catalogue.collections');

Route::get('organisation/{organisation}/employees/packers', GetPackers::class)->name('employees.packers');
Route::get('organisation/{organisation}/employees/pickers', GetPickers::class)->name('employees.pickers');

Route::get('org-agent/{orgAgent}/purchase-order/{purchaseOrder}/org-supplier-products', [GetOrgSupplierProducts::class, 'inOrgAgent'])->name('org-agent.org-supplier-products');
Route::get('org-supplier/{orgSupplier}/purchase-order/{purchaseOrder}/org-supplier-products', [GetOrgSupplierProducts::class, 'inOrgSupplier'])->name('org-supplier.org-supplier-products');

Route::get('website/{website}/unique-visitors', GetWebsiteCloudflareUniqueVisitors::class)->name('website.unique-visitors');

Route::get('delivery-recent-uploads/{palletDelivery:id}', IndexRecentPalletDeliveryUploads::class)->name('pallet_delivery.recent_uploads');

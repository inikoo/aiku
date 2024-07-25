<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Jul 2024 13:50:35 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\PalletDelivery\Json\GetFulfilmentPhysicalGoods;
use App\Actions\Fulfilment\PalletDelivery\Json\GetFulfilmentServices;
use App\Actions\Fulfilment\PalletReturn\Json\GetReturnPallets;
use App\Actions\Fulfilment\PalletReturn\Json\GetReturnStoredItems;
use App\Actions\Helpers\Tag\GetTagOptions;
use App\Actions\Mail\EmailTemplate\GetEmailTemplateCompiledLayout;
use App\Actions\Mail\EmailTemplate\GetOutboxEmailTemplates;
use App\Actions\Mail\EmailTemplate\GetSeededEmailTemplates;
use App\Actions\Mail\Mailshot\GetMailshotMergeTags;
use Illuminate\Support\Facades\Route;

Route::get('fulfilment/{fulfilment}/delivery/{scope}/services', [GetFulfilmentServices::class, 'inPalletDelivery'])->name('fulfilment.delivery.services.index');
Route::get('fulfilment/{fulfilment}/return/{scope}/services', [GetFulfilmentServices::class, 'inPalletReturn'])->name('fulfilment.return.services.index');

Route::get('fulfilment/{fulfilment}/delivery/{scope}/physical-goods', [GetFulfilmentPhysicalGoods::class, 'inPalletDelivery'])->name('fulfilment.delivery.physical-goods.index');
Route::get('fulfilment/{fulfilment}/return/{scope}/physical-goods', [GetFulfilmentPhysicalGoods::class, 'inPalletReturn'])->name('fulfilment.return.physical-goods.index');

Route::get('fulfilment/{fulfilmentCustomer}/return/stored-items', GetReturnStoredItems::class)->name('fulfilment.return.stored-items');
Route::get('fulfilment/{fulfilmentCustomer}/return/pallets', GetReturnPallets::class)->name('fulfilment.return.pallets');


Route::get('tags', GetTagOptions::class)->name('tags');

Route::get('email/templates/seeded', GetSeededEmailTemplates::class)->name('email_templates.seeded');
Route::get('email/templates/outboxes/{outbox:id}', GetOutboxEmailTemplates::class)->name('email_templates.outbox');
Route::get('email/templates/{emailTemplate:id}/compiled_layout', GetEmailTemplateCompiledLayout::class)->name('email_templates.show.compiled_layout');
Route::get('/mailshot/{mailshot:id}/merge-tags', GetMailshotMergeTags::class)->name('mailshot.merge-tags');

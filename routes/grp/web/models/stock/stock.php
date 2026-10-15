<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 29 Aug 2024 00:18:41 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Goods\TradeUnit\ApproveTradeUnitNameTranslations;
use App\Actions\Goods\TradeUnit\DeleteImageFromTradeUnit;
use App\Actions\Goods\TradeUnit\GenerateTradeUnitNameTranslations;
use App\Actions\Goods\TradeUnit\ShareTradeUnitDocuments;
use App\Actions\Goods\TradeUnit\StopSharingTradeUnitDocuments;
use App\Actions\Goods\TradeUnit\UpdateBulkTradeUnitGpsr;
use App\Actions\Goods\TradeUnit\UpdateBulkTradeUnitLabelInfo;
use App\Actions\Goods\TradeUnit\UpdateBulkTradeUnitWeightDimensions;
use App\Actions\Goods\TradeUnit\UpdateTradeUnit;
use App\Actions\Goods\TradeUnit\UpdateTradeUnitImageAlt;
use App\Actions\Goods\TradeUnit\UpdateTradeUnitImages;
use App\Actions\Goods\TradeUnit\UploadAudioToTradeUnit;
use App\Actions\Goods\TradeUnit\UploadImagesToTradeUnit;
use App\Actions\Helpers\Media\AttachAttachmentToModel;
use App\Actions\Helpers\Media\AttachImagesToModel;
use App\Actions\Helpers\Media\DetachAttachmentFromModel;
use Illuminate\Support\Facades\Route;

Route::patch('trade-units/bulk-update-label-info', UpdateBulkTradeUnitLabelInfo::class)->name('trade_units.bulk_update_label_info');
Route::patch('trade-units/bulk-update-gpsr', UpdateBulkTradeUnitGpsr::class)->name('trade_units.bulk_update_gpsr');
Route::patch('trade-units/bulk-update-weight-dimensions', UpdateBulkTradeUnitWeightDimensions::class)->name('trade_units.bulk_update_weight_dimensions');
Route::post('trade-units/bulk-translate-names', [GenerateTradeUnitNameTranslations::class, 'inBulk'])->name('trade_units.bulk_translate_names');

Route::name('trade-unit.')->prefix('trade-unit/{tradeUnit:id}')->group(function () {
    Route::patch('update', UpdateTradeUnit::class)->name('update')->withoutScopedBindings();
    Route::post('translate-names', GenerateTradeUnitNameTranslations::class)->name('translate_names');
    Route::post('approve-name-translations', ApproveTradeUnitNameTranslations::class)->name('approve_name_translations');
    Route::patch('update-images', UpdateTradeUnitImages::class)->name('update_images')->withoutScopedBindings();
    Route::post('upload-images', UploadImagesToTradeUnit::class)->name('upload_images');
    Route::post('attach-images', [AttachImagesToModel::class, 'inTradeUnit'])->name('attach_images');
    Route::post('upload-audio', UploadAudioToTradeUnit::class)->name('upload_audio');
    Route::delete('detach-image/{media:id}', DeleteImageFromTradeUnit::class)->name('detach_image')->withoutScopedBindings();
    Route::patch('media/{media:id}/alt', UpdateTradeUnitImageAlt::class)->name('update_image_alt')->withoutScopedBindings();

    Route::name('attachment.')->prefix('attachment')->group(function () {
        Route::post('attachment/attach', [AttachAttachmentToModel::class, 'inTradeUnit'])->name('attach');
        Route::delete('attachment/{attachment:id}/detach', [DetachAttachmentFromModel::class, 'inTradeUnit'])->name('detach')->withoutScopedBindings();
    });

    Route::post('share-documents', ShareTradeUnitDocuments::class)->name('share_documents');
    Route::delete('share-documents', StopSharingTradeUnitDocuments::class)->name('stop_sharing_documents');
});

<?php

/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 07 Mar 2023 11:12:51 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

use App\Actions\Helpers\Upload\DownloadUploads;
use App\Actions\Helpers\Upload\UI\ShowHistoryUpload;
use Illuminate\Support\Facades\Route;

Route::prefix('uploads')->as('uploads.')->group(function () {
    Route::get('records', ShowHistoryUpload::class)->name('records.show');
    Route::get('download', DownloadUploads::class)->name('records.download');
});

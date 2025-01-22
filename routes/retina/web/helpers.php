<?php
/*
 * author Arya Permana - Kirin
 * created on 22-01-2025-09h-36m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

use App\Actions\Retina\Helpers\Upload\DownloadRetinaUploads;
use App\Actions\Retina\Helpers\Upload\UI\ShowRetinaUpload;
use Illuminate\Support\Facades\Route;

Route::prefix('uploads/{upload}')->as('uploads.')->group(function () {
    Route::get('records', ShowRetinaUpload::class)->name('records.show');
    Route::get('download', DownloadRetinaUploads::class)->name('records.download');
});
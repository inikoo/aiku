<?php

use App\Actions\Comms\Outbox\UI\IndexOutboxes;
use App\Actions\Comms\Outbox\UI\ShowOutbox;
use App\Actions\Fulfilment\Fulfilment\UI\EditFulfilment;
use Illuminate\Support\Facades\Route;

Route::get('', EditFulfilment::class)->name('.edit');

Route::prefix('outboxes')->as('.outboxes.')->group(function () {
    Route::get('', [IndexOutboxes::class, 'inFulfilment'])->name('index');
    Route::get('{outbox}', [ShowOutbox::class, 'inFulfilment'])->name('show');
});

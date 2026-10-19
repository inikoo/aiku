<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 29-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

use App\Actions\Helpers\AI\AskBotVision;
use Illuminate\Support\Facades\Route;

Route::post('/vision', AskBotVision::class)->middleware('throttle:grp-ai-vision')->name('vision.index');

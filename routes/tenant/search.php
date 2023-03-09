<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 07 Mar 2023 11:12:51 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */


use App\Actions\Search\RunSearch;
use App\Actions\Search\ShowSearch;
use Illuminate\Support\Facades\Route;

//Route::get('/', RunSearch::class)->name('run');


Route::get('/', ShowSearch::class)->name('show');

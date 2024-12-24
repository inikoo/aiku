<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 24-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/


use App\Actions\UI\Overview\ShowOverviewHub;

Route::get('/', ShowOverviewHub::class)->name('hub');

<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 31 Jan 2025 14:28:27 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */


use App\Actions\Retina\Spaces\UI\IndexRetinaSpaces;

Route::get('', IndexRetinaSpaces::class)->name('index');

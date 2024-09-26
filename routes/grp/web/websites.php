<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 26 Sep 2024 13:20:03 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Web\Webpage\UI\ShowWebpageWorkshopPreview;

Route::get('website/{website}/webpages/{webpage}/workshop/preview', [ShowWebpageWorkshopPreview::class, 'inWebsite'])->name('preview');

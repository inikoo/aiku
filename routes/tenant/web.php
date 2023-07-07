
<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 13 Oct 2022 15:46:44 Central European Summer Time, Plane Malaga - East Midlands UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Auth\WebUser\IndexWebUser;
use App\Actions\UI\Websites\WebsitesDashboard;
use App\Actions\Web\Webpage\IndexWebpages;
use App\Actions\Web\Website\UI\CreateWebsite;
use App\Actions\Web\Website\UI\EditWebsite;
use App\Actions\Web\Website\UI\IndexWebsites;
use App\Actions\Web\Website\UI\RemoveWebsite;
use App\Actions\Web\Website\UI\ShowWebsite;
use App\Actions\Web\Website\UI\ShowWebsiteWorkshop;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', WebsitesDashboard::class)->name('dashboard');

Route::get('/websites/dashboard', WebsitesDashboard::class)->name('websites.dashboard');
Route::get('/websites', IndexWebsites::class)->name('websites.index');
Route::get('/websites/create', CreateWebsite::class)->name('websites.create');
Route::get('/webpages', [IndexWebpages::class, 'inTenant'])->name('webpages.index');


Route::get('/{website}', ShowWebsite::class)->name('websites.show');

Route::get('/{website}/edit', EditWebsite::class)->name('websites.edit');
Route::get('/{website}/workshop', ShowWebsiteWorkshop::class)->name('websites.workshop');
Route::get('/{website}/delete', RemoveWebsite::class)->name('websites.remove');


Route::get('/{website}/webpages', [IndexWebpages::class, 'inWebsite'])->name('websites.show.webpages.index');
Route::get('/{website}/webusers', [IndexWebUser::class, 'inWebsite'])->name('websites.show.webusers.index');

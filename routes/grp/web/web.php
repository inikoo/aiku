<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Jan 2024 03:24:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\CRM\WebUser\IndexWebUser;
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
Route::get('/webpages', [IndexWebpages::class, 'inOrganisation'])->name('webpages.index');


Route::get('/{website}', ShowWebsite::class)->name('websites.show');

Route::get('/{website}/edit', EditWebsite::class)->name('websites.edit');
Route::get('/{website}/workshop', ShowWebsiteWorkshop::class)->name('websites.workshop');
Route::get('/{website}/delete', RemoveWebsite::class)->name('websites.remove');


Route::get('/{website}/webpages', [IndexWebpages::class, 'inWebsite'])->name('websites.show.webpages.index');
Route::get('/{website}/webusers', [IndexWebUser::class, 'inWebsite'])->name('websites.show.webusers.index');

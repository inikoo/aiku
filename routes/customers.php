<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 16:07:09 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Sales\Customer\IndexCustomers;
use App\Actions\Sales\Customer\ShowCustomer;
use App\Actions\Web\WebUser\IndexWebUser;
use App\Actions\Web\WebUser\CreateWebUser;


Route::get('/', IndexCustomers::class)->name('index');
Route::get('/{customer}', ShowCustomer::class)->name('show');


Route::get('/{customer}/web-users', [IndexWebUser::class, 'inCustomer'])->name('show.web_users.index');
Route::get('/{customer}/web-users/create', [CreateWebUser::class, 'inCustomer'])->name('show.web_users.create');


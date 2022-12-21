<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 14 Dec 2022 18:22:40 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Actions\Dropshipping\DropshippingOrder\CreateDropshippingOrderFromIris;



Route::post('/orders', CreateDropshippingOrderFromIris::class)->name('create.order');

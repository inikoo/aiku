<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 07 Mar 2023 16:09:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Sessions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCurrentShopSession
{
    use AsAction;

    public function handle(ActionRequest $request): RedirectResponse
    {
        $request->session()->forget('currentShop');
        Session::put('reloadLayout', '1');

        return back();
    }
}

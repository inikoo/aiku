<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:54:17 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Search;

use App\Actions\InertiaAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsController;

class FlashSearchQuery extends InertiaAction
{
    use AsController;

    public function asController(Request $request): RedirectResponse
    {
        return back()->with('fastSearchQuery', $request->get('q'));
    }
}

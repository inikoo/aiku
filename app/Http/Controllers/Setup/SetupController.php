<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 12 Aug 2022 00:24:14 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Http\Controllers\Setup;

use App\Actions\Setup\SetupUsername;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SetupController extends Controller
{
    public function root(): Response
    {
        return Inertia::render('Setup/Setup');
    }


    public function setupUsername(Request $request): RedirectResponse
    {
        return SetupUsername::make()->asInertia($request->user(),$request);
    }
}

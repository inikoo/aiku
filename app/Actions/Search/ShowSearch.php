<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 11 Jan 2023 14:58:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Inikoo Ltd
 */

namespace App\Actions\Search;

use App\Actions\InertiaAction;
use App\Actions\UI\WithInertia;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsController;

class ShowSearch extends InertiaAction
{
    use AsAction;
    use WithInertia;
    use AsController;



    public function handle(): Response
    {
        return Inertia::render('Search/Search');
    }
}

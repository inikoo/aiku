<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:54:17 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Search;

use App\Actions\InertiaAction;
use App\Http\Resources\Marketing\ProductResource;
use App\Models\Marketing\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RunSearch extends InertiaAction
{
    public function handle(String $q): AnonymousResourceCollection
    {
        $items = Product::search($q)->paginate(2);

        return ProductResource::collection($items);
    }

    public function asController(Request $request): AnonymousResourceCollection
    {
        return $this->handle($request->get('q'));
    }
}

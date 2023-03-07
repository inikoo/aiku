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
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

class RunSearch extends InertiaAction
{
    /**
     * @throws Throwable
     */
    public function handle(Request $request): AnonymousResourceCollection
    {
        $q = $request->get('q');

        throw_if(is_null($q), BadRequestException::class);

        $items = Product::search($q)->get();

        return ProductResource::collection($items);
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Jul 2024 23:09:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\RetinaSearch\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Helpers\RetinaSearchResource;
use App\Models\CRM\Customer;
use App\Models\Helpers\RetinaSearch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;

class IndexRetinalSearch extends InertiaAction
{
    use AsController;


    public function handle(string $query, Customer $customer): Collection
    {
        $query = RetinaSearch::search($query)->where('customer_id', $customer->id);
        return $query->get();
    }

    public function asController(ActionRequest $request): AnonymousResourceCollection
    {
        $searchResults = $this->handle(
            query: $request->input('q', ''),
            customer: $request->user()->customer
        );

        return RetinaSearchResource::collection($searchResults);
    }


}

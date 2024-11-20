<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Jul 2024 23:07:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\UniversalSearch\UI;

use App\Actions\Helpers\UniversalSearch\Trait\WithSectionsRoute;
use App\Actions\OrgAction;
use App\Http\Resources\Helpers\UniversalSearchResource;
use App\Models\Helpers\UniversalSearch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;

class IndexUniversalSearch extends OrgAction
{
    use AsController;
    use WithSectionsRoute;

    public function handle(
        string $query,
        ?array $sections,
        ?string $organisationSlug,
        ?string $shopSlug,
        ?string $warehouseSlug,
        ?string $websiteSlug,
        ?string $fulfilmentSlug
    ): Collection {


        $query = trim($query);
        $query = preg_replace('/(\+|\-|\=|&&|\|\||\>|\<|\!|\(|\)|\{|\}|\[|\]|\^|"|~|\*|\?|\:|\\\\|\/)/', '\\\\$1', $query);
        $query = preg_replace('/\b(AND|OR|NOT)\b/', '\\\\$0', $query);
        $query = UniversalSearch::search($query)->where('group_id', group()->id);


        // dd($sections, $organisationSlug, $shopSlug, $warehouseSlug, $websiteSlug, $fulfilmentSlug);
        if ($sections && count($sections) > 0) {
            $query->whereIn('sections', $sections);
        }

        if ($organisationSlug) {
            $query->where('organisation_slug', $organisationSlug);
        }

        if ($shopSlug) {
            $query->where('shop_slug', $shopSlug);
        }

        if ($warehouseSlug) {
            $query->where('warehouse_slug', $warehouseSlug);
        }

        if ($websiteSlug) {
            $query->where('website_slug', $websiteSlug);
        }

        if ($fulfilmentSlug) {
            $query->where('fulfilment_slug', $fulfilmentSlug);
        }


        return $query->get();
    }

    public function asController(ActionRequest $request): AnonymousResourceCollection
    {
        $searchResults = $this->handle(
            query: $request->input('q', '') ?? '',
            sections: $this->parseSections($request->input('route_src')),
            organisationSlug: $request->input('organisation'),
            shopSlug: $request->input('shop'),
            warehouseSlug: $request->input('warehouse'),
            websiteSlug: $request->input('website'),
            fulfilmentSlug: $request->input('fulfilment'),
        );
        return UniversalSearchResource::collection($searchResults);
    }
}

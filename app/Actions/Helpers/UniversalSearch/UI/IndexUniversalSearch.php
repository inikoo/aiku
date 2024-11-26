<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Jul 2024 23:07:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\UniversalSearch\UI;

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

    public function parseSections($routeName): array|null
    {
        if (str_starts_with($routeName, 'grp.org.')) {
            return $this->parseOrganisationSections(
                preg_replace('/^grp\.org./', '', $routeName)
            );
        }
        if (str_starts_with($routeName, 'grp.')) {
            return $this->parseGroupSections(
                preg_replace('/^grp\./', '', $routeName)
            );
        }
        return null;
    }

    public function parseGroupSections($route): array|null
    {
        $routes = [
            'goods.stock-families.' => [],
            'goods.' => [],
        ];

        return null;
    }

    public function parseOrganisationSections($route): array|null
    {
        $routes = [
            'accounting.' => ['accounting'],
            // 'productions.' => ['productions'],
            'procurement.' => ['procurement'],
            'websites.' => ['web'],
            'fulfilments.show.web.' => ['web'],
            'fulfilments.' => ['fulfilment'],
            'shops.show.billables.' => ['billables'],
            // 'reports.' => ['reports'],
            'shops.show.catalogue.' => ['catalogue'],
            // 'shops.show.mail.' => ['mail'],
            // 'shops.show.marketing.' => ['marketing'],
            'shops.show.discounts.' => ['discounts'],
            'shops.show.ordering.' => ['ordering', 'dispatching'],
            'shops.show.web.' => ['web'],
            'shops.show.crm.' => ['crm'],
            // 'shops.' => ['assets', 'catalogue', 'mail', 'marketing', 'discounts', 'ordering', 'dispatching', 'web', 'crm', 'billables'],
            'shops.' => ['billables', 'catalogue', 'discounts', 'ordering', 'dispatching', 'web', 'crm'],
            'hr.' => ['hr'],
            'warehouses.show.infrastructure.' => ['infrastructure'],
            'warehouses.show.dispatching' => ['dispatching'],
            'warehouses.' => ['infrastructure', 'inventory', 'dispatching']
        ];

        if (empty($route) || str_starts_with($route, "dashboard.") || str_starts_with($route, "settings.")) {
            $result = collect($routes)
                    ->flatten()
                    ->unique()
                    ->values()
                    ->all();
            return $result;
        }

        foreach ($routes as $prefix => $result) {
            if (str_starts_with($route, $prefix)) {
                return $result;
            }
        }


        return null;
    }
}

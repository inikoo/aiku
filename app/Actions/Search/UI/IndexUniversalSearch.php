<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 17 Jul 2023 14:54:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Search\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\UniversalSearch\UniversalSearchResource;
use App\Models\Search\UniversalSearch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;

class IndexUniversalSearch extends InertiaAction
{
    use AsController;


    public function handle(string $query, ?array $sections, ?string $organisationSlug): Collection
    {
        $query = UniversalSearch::search($query)->where('group_id', group()->id);

        if ($sections && count($sections) > 0) {
            $query->whereIn('section',$sections);
        }


        if ($organisationSlug) {
            $query->where('organisation_slug', $organisationSlug);
        }

        return $query->get();
    }

    public function asController(ActionRequest $request): AnonymousResourceCollection
    {
        $searchResults = $this->handle(
            query: $request->input('q', ''),
            sections: $this->parseSections($request->input('route_src')),
            organisationSlug: $request->input('organisation')
        );
        return UniversalSearchResource::collection($searchResults);
    }

    public function parseSections($routeName): array|null
    {

        if (str_starts_with($routeName,'grp.org.')) {
            return $this->parseOrganisationSections(
                preg_replace('/^grp\.org./','', $routeName)
            );

        }
        return null;

    }

    public function parseOrganisationSections($route): array|null
    {

        if(str_starts_with($route,'hr.')){
            return ['hr'];
        }

        return null;
    }


}

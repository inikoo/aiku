<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:30:40 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Actions\InertiaOrganisationAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Web\Website\Hydrators\WebsiteHydrateUniversalSearch;
use App\Http\Resources\Web\WebsiteResource;
use App\Models\Market\Shop;
use App\Models\Web\Website;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdateWebsite extends InertiaOrganisationAction
{
    use WithActionUpdate;

    private Website $website;

    private bool $asAction = false;

    public function handle(Website $website, array $modelData): Website
    {
        $website = $this->update($website, $modelData, ['data', 'settings']);
        WebsiteHydrateUniversalSearch::run($website);

        return $website;
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("websites.edit");
    }


    public function rules(): array
    {
        return [
            'domain' => ['sometimes', 'required'],
            'code'   => [
                'sometimes',
                'required',
                'between:2,8',
                'alpha_dash',
                new IUnique(
                    table: 'shops',
                    extraConditions: [

                        ['column' => 'group_id', 'value' => $this->organisation->group_id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->website->id
                        ],
                    ]
                ),

            ],
            'name'   => ['sometimes', 'required']
        ];
    }

    public function action(Website $website, $modelData): Website
    {
        $this->asAction = true;
        $this->website  = $website;

        $this->initialisation($website->organisation, $modelData);

        return $this->handle($website, $this->validatedData);
    }

    public function asController(Website $website, ActionRequest $request): Website
    {
        $this->website  = $website;

        $this->initialisation($website->organisation, $request);

        return $this->handle($website, $this->validatedData);
    }


    public function inShop(Shop $shop, Website $website, ActionRequest $request): Website
    {
        $this->website  = $website;
        $this->initialisation($shop->organisation, $request);

        return $this->handle($website, $this->validatedData);
    }

    public function jsonResponse(Website $website): WebsiteResource
    {
        return new WebsiteResource($website);
    }
}

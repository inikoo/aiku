<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:30:40 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Web\Website\Search\WebsiteRecordSearch;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Http\Resources\Web\WebsiteResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Web\Website;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateWebsite extends OrgAction
{
    use WithActionUpdate;
    use HasWebAuthorisation;

    private Website $website;


    public function handle(Website $website, array $modelData): Website
    {
        $website = $this->update($website, $modelData, ['data', 'settings']);
        WebsiteRecordSearch::run($website);

        return $website;
    }


    public function rules(): array
    {
        return [
            'domain'          => [
                'sometimes',
                'required',
                'ascii',
                'lowercase',
                'max:255',
                new IUnique(
                    table: 'websites',
                    extraConditions: [
                        [
                            'column' => 'group_id',
                            'value'  => $this->organisation->group_id
                        ],
                        [
                            'column'    => 'status',
                            'operation' => '=',
                            'value'     => true
                        ],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->website->id
                        ],
                    ]
                ),
            ],
            'code'            => [
                'sometimes',
                'required',
                'ascii',
                'lowercase',
                'max:64',
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
            'name'            => ['sometimes', 'required', 'string', 'max:255'],
            'launched_at'     => ['sometimes', 'date'],
            'state'           => ['sometimes', Rule::enum(WebsiteStateEnum::class)],
            'status'          => ['sometimes', 'boolean'],
            'last_fetched_at' => ['sometimes', 'date'],
        ];
    }

    public function action(Website $website, array $modelData, bool $audit = true): Website
    {
        if (!$audit) {
            Website::disableAuditing();
        }
        $this->asAction = true;
        $this->website  = $website;

        $this->initialisation($website->organisation, $modelData);

        return $this->handle($website, $this->validatedData);
    }

    public function asController(Website $website, ActionRequest $request): Website
    {
        $this->scope   = $website->shop;
        $this->website = $website;
        $this->initialisationFromShop($website->shop, $request);

        return $this->handle($website, $this->validatedData);
    }


    public function inFulfilment(Fulfilment $fulfilment, Website $website, ActionRequest $request): Website
    {
        $this->scope   = $fulfilment;
        $this->website = $website;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($website, $this->validatedData);
    }

    public function jsonResponse(Website $website): WebsiteResource
    {
        return new WebsiteResource($website);
    }
}

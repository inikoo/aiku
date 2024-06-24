<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Jan 2024 10:31:14 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Web\Webpage\Hydrators\WebpageHydrateUniversalSearch;
use App\Enums\Web\Webpage\WebpagePurposeEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Http\Resources\Web\WebpageResource;
use App\Models\Catalogue\Shop;
use App\Models\Web\Webpage;
use App\Rules\AlphaDashSlash;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateWebpage extends OrgAction
{
    use WithActionUpdate;

    private Webpage $webpage;


    public function handle(Webpage $webpage, array $modelData): Webpage
    {
        $webpage = $this->update($webpage, $modelData, ['data', 'settings']);

        WebpageHydrateUniversalSearch::run($webpage);

        return $webpage;
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("web.{$this->shop->id}.edit");
    }


    public function rules(): array
    {
        return [
            'url' => [
                'sometimes',
                'required',
                'ascii',
                'lowercase',
                'max:255',
                new AlphaDashSlash(),
                new IUnique(
                    table: 'webpages',
                    extraConditions: [
                        [
                            'column' => 'website_id',
                            'value'  => $this->webpage->website->id
                        ],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->webpage->id
                        ],
                    ]
                ),
            ],
            'code' => [
                'sometimes',
                'required',
                'ascii',
                'max:64',
                'alpha_dash',
                new IUnique(
                    table: 'shops',
                    extraConditions: [

                        ['column' => 'website_id', 'value' => $this->webpage->website_id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->webpage->id
                        ],
                    ]
                ),

            ],
            'level'         => ['sometimes', 'integer'],
            'purpose'       => ['sometimes', Rule::enum(WebpagePurposeEnum::class)],
            'type'          => ['sometimes', Rule::enum(WebpageTypeEnum::class)],
            'state'         => ['sometimes', Rule::enum(WebpageStateEnum::class)],
            'google_search' => ['sometimes', 'array'],
            'ready_at'      => ['sometimes', 'date'],
            'live_at'       => ['sometimes', 'date'],
        ];
    }

    public function action(Webpage $webpage, $modelData): Webpage
    {
        $this->asAction = true;
        $this->webpage  = $webpage;

        $this->initialisation($webpage->organisation, $modelData);

        return $this->handle($webpage, $this->validatedData);
    }

    public function asController(Webpage $webpage, ActionRequest $request): Webpage
    {
        $this->webpage = $webpage;

        $this->initialisation($webpage->organisation, $request);

        return $this->handle($webpage, $this->validatedData);
    }


    public function inShop(Shop $shop, Webpage $webpage, ActionRequest $request): Webpage
    {
        $this->webpage = $webpage;
        $this->initialisationFromShop($shop, $request);

        $modelData = [];
        foreach ($this->validatedData as $key => $value) {
            data_set(
                $modelData,
                match ($key) {
                    'google_search'  => 'data',
                    default          => $key
                },
                $value
            );
        }

        return $this->handle($webpage, $modelData);
    }

    public function jsonResponse(Webpage $webpage): WebpageResource
    {
        return new WebpageResource($webpage);
    }
}

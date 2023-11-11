<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:30:40 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Actions\Central\Central\StoreDomain;
use App\Actions\Organisation\Organisation\Hydrators\OrganisationHydrateWeb;
use App\Actions\Web\Website\Hydrators\WebsiteHydrateUniversalSearch;
use App\Models\Market\Shop;
use App\Models\Web\Website;
use App\Rules\CaseSensitive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreWebsite
{
    use AsAction;
    use WithAttributes;


    /**
     * @var true
     */
    private bool $asAction = false;
    private mixed $shop;


    public function handle(Shop $shop, array $modelData): Website
    {
        data_set($modelData, 'type', $shop->subtype);
        /** @var Website $website */
        $website = $shop->website()->create($modelData);
        $website->webStats()->create();
        StoreDomain::run(app('currentTenant'), $website, [
            'slug'   => $website->code,
            'domain' => $website->domain
        ]);
        OrganisationHydrateWeb::dispatch(app('currentTenant'));
        WebsiteHydrateUniversalSearch::dispatch($website);
        return $website;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.edit");
    }

    public function rules(): array
    {
        return [
            'domain' => ['required', new CaseSensitive('websites')],
            'code'   => ['required', 'unique:tenant.websites','max:8'],
            'name'   => ['required']
        ];
    }

    public function asController(Shop $shop, ActionRequest $request): Website
    {
        $this->shop = $shop;
        $request->validate();

        return $this->handle($shop, $request->validated());
    }

    public function afterValidator(Validator $validator): void
    {
        if ($this->shop->website) {
            $validator->errors()->add('domain', 'This shop already have a website');
        }
    }

    public function htmlResponse(Website $website): RedirectResponse
    {
        return Redirect::route('websites.show', [
            $website->slug
        ]);
    }

    public function action(Shop $parent, array $objectData): Website
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $validatedData);
    }
}

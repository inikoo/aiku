<?php
/*
 * author Arya Permana - Kirin
 * created on 12-03-2025-11h-13m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Web\Redirect;

use App\Actions\Catalogue\Asset\StoreAsset;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateServices;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateServices;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateServices;
use App\Enums\Billables\Rental\RentalStateEnum;
use App\Enums\Billables\Service\ServiceStateEnum;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Web\Redirect\RedirectTypeEnum;
use App\Models\Billables\Service;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Redirect;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreRedirect extends OrgAction
{
    public function handle(Website|Webpage $parent, array $modelData): Redirect
    {
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'shop_id', $parent->shop_id);
        if($parent instanceof Webpage)
        {
            data_set($modelData, 'website_id', $parent->website_id);
        }

        /** @var Redirect $redirect */
        $redirect = $parent->redirects()->create($modelData);

        return $redirect;
    }

    public function rules(): array
    {
        return [
            'type'                     => ['required', Rule::enum(RedirectTypeEnum::class)],
            'url'                      => ['required', 'string'],
            'path'                     => ['required', 'string'],
        ];
    }

    public function action(Website|Webpage $parent, array $modelData): Redirect
    {
        $this->asAction       = true;
        $this->initialisationFromShop($parent->shop, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

    public function asController(Website $website, ActionRequest $request)
    {
        $this->initialisationFromShop($website->shop, $request);

        return $this->handle($website, $this->validatedData);
    }

    public function inWebpage(Webpage $webpage, ActionRequest $request)
    {
        $this->initialisationFromShop($webpage->shop, $request);

        return $this->handle($webpage, $this->validatedData);
    }


}

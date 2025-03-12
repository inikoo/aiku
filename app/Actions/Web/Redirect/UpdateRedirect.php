<?php
/*
 * author Arya Permana - Kirin
 * created on 12-03-2025-11h-27m
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
use App\Actions\Traits\WithActionUpdate;
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
use App\Models\Web\Website;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateRedirect extends OrgAction
{
    use WithActionUpdate;

    public function handle(Redirect $redirect, array $modelData): Redirect
    {
        $redirect = $this->update($redirect, $modelData);

        return $redirect;
    }

    public function rules(): array
    {
        return [
            'type'                     => ['sometimes', Rule::enum(RedirectTypeEnum::class)],
            'url'                      => ['sometimes', 'string'],
            'path'                     => ['sometimes', 'string'],
        ];
    }

    public function action(Redirect $redirect, array $modelData): Redirect
    {
        $this->asAction       = true;
        $this->initialisationFromShop($redirect->shop, $modelData);

        return $this->handle($redirect, $this->validatedData);
    }

    public function asController(Redirect $redirect, ActionRequest $request)
    {
        $this->initialisationFromShop($redirect->shop, $request);

        return $this->handle($redirect, $this->validatedData);
    }


}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:15 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Portfolio\Banner;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateBanners;
use App\Actions\Portfolio\Banner\Hydrators\BannerHydrateUniversalSearch;
use App\Actions\Portfolio\PortfolioWebsite\Hydrators\PortfolioWebsiteHydrateBanners;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Portfolio\BannerResource;
use App\Models\Portfolio\Banner;
use Lorisleiva\Actions\ActionRequest;

class UpdateBanner
{
    use WithActionUpdate;

    public bool $isAction = false;

    public function handle(Banner $banner, array $modelData): Banner
    {
        $this->update($banner, $modelData, ['data']);

        BannerHydrateUniversalSearch::dispatch($banner);
        CustomerHydrateBanners::dispatch(customer());

        foreach($banner->portfolioWebsites as $portfolioWebsite) {
            PortfolioWebsiteHydrateBanners::run($portfolioWebsite);
        }

        return $banner;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->isAction) {
            return true;
        }

        return $request->get('customerUser')->hasPermissionTo("portfolio.banners.edit");
    }

    public function rules(): array
    {
        return [
            'name'                 => ['sometimes', 'required','string','max:255'],
            'portfolio_website_id' => ['nullable']
        ];
    }


    public function asController(Banner $banner, ActionRequest $request): Banner
    {
        $request->validate();

        return $this->handle($banner, $request->validated());
    }

    public function action(Banner $banner, $modelData): Banner
    {
        $this->isAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($banner, $validatedData);
    }

    public function jsonResponse(Banner $website): BannerResource
    {
        return new BannerResource($website);
    }
}

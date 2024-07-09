<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:14 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Banner;


use App\Models\CRM\Customer;
use App\Models\Web\Banner;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteBanner
{
    use AsAction;
    use WithAttributes;

    public bool $isAction = false;

    public function handle(Customer $customer, Banner $banner): Banner
    {
        $banner->delete();


        return $banner;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->isAction) {
            return true;
        }

        return $request->get('customerUser')->hasPermissionTo("portfolio.banners.edit");
    }

    public function action(Customer $customer, Banner $banner): Banner
    {
        return $this->handle($customer, $banner);
    }

    public function asController(Banner $banner, ActionRequest $request): Banner
    {
        $request->validate();

        return $this->handle($request->get('customer'), $banner);
    }


    public function htmlResponse(Banner $banner): RedirectResponse
    {
        return redirect()->route(
            'customer.banners.banners.index',
        );
    }
}

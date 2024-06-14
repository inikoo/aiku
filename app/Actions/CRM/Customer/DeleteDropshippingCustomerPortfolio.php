<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Fri, 14 Jun 2024 09:33:25 Indonesia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer;

use App\Actions\OrgAction;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\DropshippingCustomerPortfolio;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteDropshippingCustomerPortfolio extends OrgAction
{
    use AsController;
    use WithAttributes;


    public function handle (DropshippingCustomerPortfolio $portfolio): DropshippingCustomerPortfolio
    {
        $portfolio->stats()->delete();
        $portfolio->delete();

        return $portfolio;
    }

    public function asController(Organisation $organisation, Shop $shop, Customer $customer, DropshippingCustomerPortfolio $portfolio, ActionRequest $request): DropshippingCustomerPortfolio
    {
        $this->initialisationFromShop($shop, $request);
        $request->validate();

        return $this->handle($portfolio);
    }



    public function htmlResponse(Customer $customer): RedirectResponse
    {
        return Redirect::route('grp.org.shops.show.crm.customers.show.portfolios.index', [$customer->organisation->slug, $customer->shop->slug, $customer->slug]);
    }
}
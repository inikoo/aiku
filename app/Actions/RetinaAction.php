<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Feb 2024 20:59:47 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions;

use App\Actions\Traits\WithTab;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class RetinaAction
{
    use AsAction;
    use WithAttributes;
    use WithTab;


    protected Website $website;
    protected Customer $customer;
    protected WebUser $webUser;
    protected Fulfilment $fulfilment;
    protected Organisation $organisation;
    protected Shop $shop;


    protected array $validatedData;


    public function initialisation(ActionRequest $request): static
    {
        $this->webUser       = $request->user();
        $this->customer      = $this->webUser->customer;
        $this->shop          = $this->customer->shop;
        $this->fulfilment    = $this->shop->fulfilment;
        $this->organisation  = $this->shop->organisation;
        $this->website       = $request->get('website');
        $this->fillFromRequest($request);

        $this->validatedData = $this->validateAttributes();

        return $this;
    }

    public function initialisationFulfilmentActions(FulfilmentCustomer $fulfilmentCustomer, array $modelData): static
    {
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;
        $this->customer     = $fulfilmentCustomer->customer;
        $this->shop         = $this->fulfilment->shop;
        $this->organisation = $this->fulfilment->organisation;
        $this->setRawAttributes($modelData);
        $this->validatedData = $this->validateAttributes();

        return $this;
    }


}

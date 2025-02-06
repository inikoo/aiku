<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Feb 2024 20:59:47 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions;

use App\Actions\Traits\WithTab;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Illuminate\Support\Str;
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
    protected ?Fulfilment $fulfilment;
    protected ?FulfilmentCustomer $fulfilmentCustomer;
    protected Organisation $organisation;
    protected Shop $shop;


    protected array $validatedData;


    public function registerInitialisation(Fulfilment $fulfilment, ActionRequest $request): static
    {
        $this->fulfilment    = $fulfilment;
        $this->shop          = $this->fulfilment->shop;
        $this->organisation  = $this->shop->organisation;
        $this->website       = $request->get('website');
        $this->fillFromRequest($request);
        $this->validatedData = $this->validateAttributes();

        return $this;
    }

    public function initialisation(ActionRequest $request): static
    {
        $this->webUser       = $request->user();
        $this->customer      = $this->webUser->customer;
        $this->fulfilmentCustomer = $this->customer->fulfilmentCustomer;
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
        $this->fulfilmentCustomer = $fulfilmentCustomer;
        $this->customer     = $fulfilmentCustomer->customer;
        $this->shop         = $this->fulfilment->shop;
        $this->organisation = $this->fulfilment->organisation;
        $this->webUser      = $this->customer->webUsers()->first();
        $this->setRawAttributes($modelData);
        $this->validatedData = $this->validateAttributes();

        return $this;
    }

    public function logoutInitialisation(ActionRequest $request): static
    {

        $this->website       = $request->get('website');

        $this->shop          = $this->website->shop;
        $this->fulfilment    = $this->shop->fulfilment;
        $this->organisation  = $this->shop->organisation;

        $this->fillFromRequest($request);

        $this->validatedData = $this->validateAttributes();

        return $this;
    }

    public function authorize(ActionRequest $request)
    {
        // Define the segments or route names that should always be accessible
        $publicRoutes = ['login', 'register', 'profile', 'logout', 'home', 'dashboard', 'password'];

        // Option 1: Check if the route's name is in the list.
        if ($request->route() && in_array($request->route()->getName(), $publicRoutes, true)) {
            return true;
        }

        // Option 2: Alternatively, check if the URL path contains any of these segments.
        foreach ($publicRoutes as $segment) {
            if (Str::contains($request->path(), $segment)) {
                return true;
            }
        }

        // Otherwise, apply the additional authorization logic.
        if ($this->webUser->customer->status === CustomerStatusEnum::APPROVED
            && $this->fulfilmentCustomer->rentalAgreement) {
            return true;
        }

        // Deny access if none of the above conditions pass.
        return false;
    }



}

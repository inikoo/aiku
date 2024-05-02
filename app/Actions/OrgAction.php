<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 17 Sept 2022 02:10:19 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions;

use App\Actions\Traits\WithTab;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Inventory\Warehouse;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithTab;


    protected Organisation $organisation;
    protected Shop $shop;
    protected Fulfilment $fulfilment;
    protected Warehouse $warehouse;

    protected bool $asAction         = false;
    protected bool $canEdit          = false;
    protected bool $canDelete        = false;
    protected bool $isSupervisor     = false;
    public int $hydratorsDelay       = 0;
    protected bool $strict           = true;

    protected array $validatedData;


    public function initialisation(Organisation $organisation, ActionRequest|array $request): static
    {
        $this->organisation = $organisation;
        if (is_array($request)) {
            $this->setRawAttributes($request);
        } else {
            $this->fillFromRequest($request);
        }
        $this->validatedData = $this->validateAttributes();

        return $this;
    }

    public function initialisationFromShop(Shop $shop, ActionRequest|array $request): static
    {
        $this->shop         = $shop;
        $this->organisation = $shop->organisation;
        if (is_array($request)) {
            $this->setRawAttributes($request);
        } else {
            $this->fillFromRequest($request);
        }
        $this->validatedData = $this->validateAttributes();

        return $this;
    }

    public function initialisationFromFulfilment(Fulfilment $fulfilment, ActionRequest|array $request): static
    {
        $this->fulfilment         = $fulfilment;
        $this->shop               = $fulfilment->shop;
        $this->organisation       = $fulfilment->organisation;
        if (is_array($request)) {
            $this->setRawAttributes($request);
        } else {
            $this->fillFromRequest($request);
        }
        $this->validatedData = $this->validateAttributes();

        return $this;
    }

    public function initialisationFromWarehouse(Warehouse $warehouse, ActionRequest|array $request): static
    {
        $this->warehouse    = $warehouse;
        $this->organisation = $warehouse->organisation;
        if (is_array($request)) {
            $this->setRawAttributes($request);
        } else {
            $this->fillFromRequest($request);
        }
        $this->validatedData = $this->validateAttributes();

        return $this;
    }


}

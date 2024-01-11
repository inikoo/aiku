<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 17 Sept 2022 02:10:19 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions;

use App\Actions\Traits\WithTab;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class InertiaOrganisationAction
{
    use AsAction;
    use WithAttributes;
    use WithTab;



    protected Organisation $organisation;
    protected Shop $shop;
    protected bool $canEdit               = false;
    protected bool $canDelete             = false;
    protected array $validatedData;


    public function initialisation(Organisation $organisation, ActionRequest|array $request): static
    {
        $this->organisation          = $organisation;
        if(is_array($request)) {
            $this->setRawAttributes($request);
        } else {
            $this->fillFromRequest($request);

        }
        $this->validatedData=$this->validateAttributes();

        return $this;
    }

    public function initialisationFromShop(Shop $shop, ActionRequest|array $request): static
    {
        $this->shop          = $shop;
        $this->organisation  = $shop->organisation;
        if(is_array($request)) {
            $this->setRawAttributes($request);
        } else {
            $this->fillFromRequest($request);

        }
        $this->validatedData=$this->validateAttributes();

        return $this;
    }




}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Jan 2024 20:43:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions;

use App\Models\Market\Shop;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class InertiaShopAction
{
    use AsAction;
    use WithAttributes;



    protected Shop $shop;
    protected ?string $tab                = null;
    protected bool $canEdit               = false;
    protected bool $canDelete             = false;
    protected array $validatedData;

    public function initialisation(Shop $shop, ActionRequest|array $request): static
    {
        $this->shop          = $shop;
        if(is_array($request)) {
            $this->setRawAttributes($request);
        } else {
            $this->fillFromRequest($request);

        }
        $this->validatedData=$this->validateAttributes();

        return $this;
    }

    public function withTab(array $tabs): static
    {
        $tab =  $this->get('tab', Arr::first($tabs));

        if (!in_array($tab, $tabs)) {
            abort(404);
        }
        $this->tab = $tab;

        return $this;
    }


}

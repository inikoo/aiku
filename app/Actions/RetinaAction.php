<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Feb 2024 20:59:47 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions;

use App\Actions\Traits\WithTab;
use App\Actions\UI\WithInertia;
use App\Models\CRM\Customer;
use App\Models\Web\Website;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class RetinaAction
{
    use AsAction;
    use WithAttributes;
    use WithTab;
    use WithInertia;


    protected Website $website;
    protected Customer $customer;


    protected array $validatedData;


    public function initialisation(ActionRequest $request): static
    {
        $this->customer      =$request->user()->customer;
        $this->website       =$request->get('website');
        $this->fillFromRequest($request);
        $this->validatedData = $this->validateAttributes();

        return $this;
    }


}

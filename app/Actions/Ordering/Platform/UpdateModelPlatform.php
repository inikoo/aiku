<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:32 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Platform;

use App\Actions\GrpAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\DropshippingCustomerPortfolio;
use App\Models\Ordering\Order;
use App\Models\Ordering\Platform;
use App\Models\SysAdmin\Group;

class UpdateModelPlatform extends GrpAction
{
    use WithActionUpdate;

    public function handle(Customer|Order|DropshippingCustomerPortfolio $model, Platform $platform): Customer|Order|DropshippingCustomerPortfolio
    {
        $currentPlatform = $model->platform();

        $model->platforms()->detach($currentPlatform->id);
        $model->platforms()->attach($platform->id);

        return $model;
    }

    public function rules(): array
    {
        return [];
    }

    public function action(Group $group, Customer|Order|DropshippingCustomerPortfolio $model, Platform $platform, array $modelData): Customer|Order|DropshippingCustomerPortfolio
    {
        $this->initialisation($group, $modelData);

        return $this->handle($model, $platform);
    }
}

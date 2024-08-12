<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:11:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\TopUp;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateTopUps;
use App\Actions\CRM\Customer\Hydrators\CustomerHydrateTopUps;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateTopUps;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateTopUps;
use App\Models\Accounting\Payment;
use App\Models\Accounting\TopUp;

class StoreTopUp extends OrgAction
{

    public function handle(Payment $payment, array $modelData): TopUp
    {
        data_set($modelData, 'group_id', $payment->group_id);
        data_set($modelData, 'organisation_id', $payment->organisation_id);
        data_set($modelData, 'currency_id', $payment->shop->currency_id);
        data_set($modelData, 'customer_id', $payment->customer_id);
        data_set($modelData, 'shop_id', $payment->shop_id);

        $topUp = $payment->topUps()->create($modelData);

        $topUp->refresh();

        CustomerHydrateTopUps::dispatch($topUp->customer);
        ShopHydrateTopUps::dispatch($topUp->shop);
        OrganisationHydrateTopUps::dispatch($topUp->organisation);
        GroupHydrateTopUps::dispatch($topUp->group);

        return $topUp;
    }

    public function rules()
    {
        return [
            'amount'           => ['required', 'numeric'],
            'number'           => ['sometimes', 'string'],
            'source_id'        => ['sometimes', 'string'],
        ];
    }

    public function action(Payment $payment, $modelData): TopUp
    {
        $this->asAction = true;
        $this->initialisation($payment->organisation, $modelData);
        return $this->handle($payment, $modelData);
    }
}
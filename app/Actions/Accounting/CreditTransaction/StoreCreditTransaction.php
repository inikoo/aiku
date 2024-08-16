<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:11:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\CreditTransaction;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCreditTransactions;
use App\Actions\CRM\Customer\Hydrators\CustomerHydrateCreditTransactions;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateCreditTransactions;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateCreditTransactions;
use App\Actions\Traits\WithOrderExchanges;
use App\Enums\Accounting\Invoice\CreditTransactionTypeEnum;
use App\Models\Accounting\CreditTransaction;
use App\Models\CRM\Customer;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreCreditTransaction extends OrgAction
{
    use AsAction;
    use WithOrderExchanges;

    public function handle(Customer $customer, array $modelData): CreditTransaction
    {
        data_set($modelData, 'group_id', $customer->group_id);
        data_set($modelData, 'organisation_id', $customer->organisation_id);
        data_set($modelData, 'shop_id', $customer->shop_id);
        data_set($modelData, 'currency_id', $customer->shop->currency_id);
        data_set($modelData, 'date', now(), overwrite: false);

        $modelData = $this->processExchanges($modelData, $customer->shop, 'amount');

        /** @var CreditTransaction $creditTransaction */
        $creditTransaction = $customer->creditTransactions()->create($modelData);

        if($this->hydratorsDelay>0) {
            CustomerHydrateCreditTransactions::dispatch($customer)->delay($this->hydratorsDelay);

        } else {
            CustomerHydrateCreditTransactions::run($customer);
        }

        ShopHydrateCreditTransactions::dispatch($creditTransaction->shop)->delay($this->hydratorsDelay);
        OrganisationHydrateCreditTransactions::dispatch($creditTransaction->organisation)->delay($this->hydratorsDelay);
        GroupHydrateCreditTransactions::dispatch($creditTransaction->group)->delay($this->hydratorsDelay);

        return $creditTransaction;
    }

    public function rules(): array
    {
        $rules= [
            'amount'     => ['required', 'numeric'],
            'date'       => ['sometimes', 'date'],
            'type'       => ['required', Rule::enum(CreditTransactionTypeEnum::class)],
            'source_id'  => ['sometimes', 'string'],
            'payment_id' => [
                'sometimes',
                'nullable',
                Rule::exists('payments', 'id')
                    ->where('shop_id', $this->shop->id)
            ],
            'top_up_id'  => [
                'sometimes',
                'nullable',
                Rule::exists('top_ups', 'id')
                    ->where('shop_id', $this->shop->id)
            ],
            'fetched_at'  => ['sometimes', 'date'],
        ];
        if (!$this->strict) {
            $rules['grp_exchange'] = ['sometimes', 'numeric'];
            $rules['org_exchange'] = ['sometimes', 'numeric'];

        }

        return $rules;

    }

    public function action(Customer $customer, $modelData, int $hydratorsDelay = 0, bool $strict = true): CreditTransaction
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($customer->shop, $modelData);

        return $this->handle($customer, $modelData);
    }
}

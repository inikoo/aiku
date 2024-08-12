<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:11:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\CreditTransaction;

use AlibabaCloud\SDK\Dm\V20151123\Models\GetIpfilterListResponseBody\data;
use App\Actions\OrgAction;
use App\Enums\Accounting\Invoice\CreditTransactionTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Service;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreCreditTransaction extends OrgAction
{
    use AsAction;

    public function handle(Customer $customer, array $modelData): void
    {
        data_set($modelData, 'group_id', $customer->group_id);
        data_set($modelData, 'organisation_id', $customer->organisation_id);
        data_set($modelData, 'shop_id', $customer->shop_id);
        data_set($modelData, 'currency_id', $customer->shop->currency_id);
        data_set($modelData, 'date', now(), overwrite:false);

        $amount = Arr::get($modelData, 'amount');
        $latestCreditTransaction = $customer->creditTransactions()
        ->orderBy('created_at', 'desc')
        ->first();

        $runningAmount = $latestCreditTransaction ? $latestCreditTransaction->running_amount : 0;
        $newRunningAmount = $runningAmount + $amount;

        data_set($modelData, 'running_amount', $newRunningAmount );

        $customer->creditTransactions()->create($modelData);
    }

    public function rules()
    {
        return [
            'amount'           => ['required', 'numeric'],
            'date'             => ['sometimes', 'date'],
            'type'             => ['required', Rule::enum(CreditTransactionTypeEnum::class)],
            'source_id'        => ['sometimes', 'string'],
            'payment_id'       => ['sometimes', 'nullable', 
                                        Rule::exists('payments', 'id')
                                                ->where('shop_id', $this->shop->id)
                                    ],
            'top_up_id'           => ['sometimes', 'nullable',
                                        Rule::exists('top_ups', 'id')
                                                ->where('shop_id', $this->shop->id)
                                    ]
        ];
    }

    public function action(Customer $customer, $modelData): void
    {
        $this->asAction = true;
        $this->initialisationFromShop($customer->shop, $modelData);
        $this->handle($customer, $modelData);
    }
}
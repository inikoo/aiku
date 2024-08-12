<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:11:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\CreditTransaction;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Accounting\Invoice\CreditTransactionTypeEnum;
use App\Models\Accounting\CreditTransaction;
use Illuminate\Validation\Rule;

class UpdateCreditTransaction extends OrgAction
{
    use WithActionUpdate;

    public function handle(CreditTransaction $creditTransaction, array $modelData): CreditTransaction
    {
        return $this->update($creditTransaction, $modelData);

    }

    public function rules(): array
    {
        return [
            'amount'           => ['sometimes', 'numeric'],
            'date'             => ['sometimes', 'date'],
            'type'             => ['sometimes', Rule::enum(CreditTransactionTypeEnum::class)],
            'source_id'        => ['sometimes', 'string'],
            'notes'            => ['sometimes', 'string'],
            'payment_id'       => ['sometimes',
                                    Rule::exists('payments', 'id')
                                            ->where('shop_id', $this->shop->id)
                                ],
            'top_up_id'           => ['sometimes',
                                    Rule::exists('top_ups', 'id')
                                            ->where('shop_id', $this->shop->id)
                                ]
            ];
    }

    public function action(CreditTransaction $creditTransaction, $modelData): CreditTransaction
    {
        $this->asAction = true;
        $this->initialisationFromShop($creditTransaction->shop, $modelData);
        return $this->handle($creditTransaction, $modelData);
    }
}

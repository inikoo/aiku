<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:11:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\CreditTransaction;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Accounting\Invoice\CreditTransactionTypeEnum;
use App\Models\Accounting\CreditTransaction;
use Illuminate\Validation\Rule;

class UpdateCreditTransaction extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;

    public function handle(CreditTransaction $creditTransaction, array $modelData): CreditTransaction
    {
        return $this->update($creditTransaction, $modelData);
    }

    public function rules(): array
    {
        $rules = [
            'amount'          => ['sometimes', 'numeric'],
            'date'            => ['sometimes', 'date'],
            'type'            => ['sometimes', Rule::enum(CreditTransactionTypeEnum::class)],
            'notes'           => ['sometimes', 'string'],
            'payment_id'      => [
                'sometimes',
                Rule::exists('payments', 'id')
                    ->where('shop_id', $this->shop->id)
            ],
            'top_up_id'       => [
                'sometimes',
                Rule::exists('top_ups', 'id')
                    ->where('shop_id', $this->shop->id)
            ],
        ];

        if (!$this->strict) {
            $rules = $this->noStrictUpdateRules($rules);
        }

        return $rules;
    }

    public function action(CreditTransaction $creditTransaction, array $modelData, int $hydratorsDelay = 0, bool $strict = true): CreditTransaction
    {
        $this->strict = $strict;

        $this->asAction = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($creditTransaction->shop, $modelData);

        return $this->handle($creditTransaction, $modelData);
    }
}

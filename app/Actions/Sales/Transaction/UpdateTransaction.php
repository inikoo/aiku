<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 13:34:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Transaction;

use App\Actions\WithActionUpdate;
use App\Models\Sales\Transaction;

class UpdateTransaction
{
    use WithActionUpdate;

    public function handle(Transaction $transaction, array $modelData): Transaction
    {
        return $this->update($transaction, $modelData, ['data']);
    }

    public function rules(): array
    {
        return [
            'type'             => ['required'],
            'quantity_bonus'   => ['required', 'numeric'],
            'quantity_ordered' => ['required', 'numeric'],
        ];
    }

    public function action(Transaction $transaction, array $objectData): Transaction
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($transaction, $validatedData);
    }
}

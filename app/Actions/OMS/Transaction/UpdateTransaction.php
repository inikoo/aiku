<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:32 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\Transaction;

use App\Actions\Traits\WithActionUpdate;
use App\Models\OMS\Transaction;

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

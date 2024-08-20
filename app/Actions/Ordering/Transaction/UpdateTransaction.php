<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:32 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Transaction;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Ordering\Transaction\TransactionFailStatusEnum;
use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Enums\Ordering\Transaction\TransactionStatusEnum;
use App\Models\Ordering\Transaction;
use Illuminate\Validation\Rule;

class UpdateTransaction extends OrgAction
{
    use WithActionUpdate;

    public function handle(Transaction $transaction, array $modelData): Transaction
    {
        return $this->update($transaction, $modelData, ['data']);
    }

    public function rules(): array
    {
        return [
            'quantity_ordered'         => ['sometimes','required', 'numeric', 'min:0'],
            'quantity_bonus'           => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_dispatched'      => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_fail'            => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_cancelled'       => ['sometimes', 'sometimes', 'numeric', 'min:0'],
            'source_id'                => ['sometimes', 'string'],
            'state'                    => ['sometimes', Rule::enum(TransactionStateEnum::class)],
            'status'                   => ['sometimes', Rule::enum(TransactionStatusEnum::class)],
            'fail_status'              => ['sometimes', 'nullable',Rule::enum(TransactionFailStatusEnum::class)],
            'gross_amount'             => ['sometimes','required', 'numeric'],
            'net_amount'               => ['sometimes','required', 'numeric'],
            'org_exchange'             => ['sometimes', 'numeric'],
            'grp_exchange'             => ['sometimes', 'numeric'],
            'org_net_amount'           => ['sometimes', 'numeric'],
            'grp_net_amount'           => ['sometimes', 'numeric'],
            'created_at'               => ['sometimes', 'required', 'date'],
            'tax_category_id'          => ['sometimes', 'required', 'exists:tax_categories,id'],
            'date'                     => ['sometimes', 'required', 'date'],
            'submitted_at'             => ['sometimes', 'required', 'date'],
            'last_fetched_at'          => ['sometimes', 'date'],
        ];
    }

    public function action(Transaction $transaction, array $modelData): Transaction
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($transaction, $validatedData);
    }
}

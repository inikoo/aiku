<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\Hydrators;

use App\Actions\CRM\Customer\UpdateCustomer;
use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\Customer;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateCreditTransactions
{
    use AsAction;
    use WithActionUpdate;
    private Customer $customer;
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->customer->id))->dontRelease()];
    }

    public function handle(Customer $customer): void
    {
        $balance            = 0;
        $creditTransactions = $customer->creditTransactions()
        ->orderBy('date', 'asc')
        ->get();
        foreach($creditTransactions as $creditTransaction) {
            $balance += $creditTransaction->amount;
            $this->update($creditTransaction, [
                'running_amount' => $balance
            ]);
        }
        data_set($modelData, 'balance', $balance);

        UpdateCustomer::make()->action($customer, $modelData);
    }


}

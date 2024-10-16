<?php
/*
 * author Arya Permana - Kirin
 * created on 16-10-2024-11h-37m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\CRM\Customer\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\CRM\Customer;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateBackInStockReminders
{
    use AsAction;
    use WithEnumStats;

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
        $stats = [
            'number_reminders' => $customer->backInStockReminder()->whereNull('unreminded_at')->count(),
            'number_unreminded' => $customer->backInStockReminder()->whereNotNull('unreminded_at')->count(),
        ];

        $customer->stats()->update($stats);
    }

}

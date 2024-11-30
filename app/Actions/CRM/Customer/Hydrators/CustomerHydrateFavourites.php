<?php

/*
 * author Arya Permana - Kirin
 * created on 14-10-2024-08h-46m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\CRM\Customer\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\CRM\Customer;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateFavourites
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
            'number_favourites' => $customer->favourites()->whereNull('unfavourited_at')->count(),
            'number_unfavourited' => $customer->favourites()->whereNotNull('unfavourited_at')->count(),
        ];

        $customer->stats()->update($stats);
    }

}

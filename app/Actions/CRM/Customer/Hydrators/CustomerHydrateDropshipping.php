<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\Hydrators;

use App\Models\CRM\Customer;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateDropshipping
{
    use AsAction;
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
            'number_portfolios'                                  => $customer->portfolios()->count(),
            'number_current_portfolios'                          => $customer->portfolios()->where('status', true)->count(),
            'number_products'                                    => $customer->products()->count(),
            'number_current_products'                            => $customer->products()->where('status', true)->count(),
        ];

        $customer->dropshippingStats()->update($stats);
    }
}

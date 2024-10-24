<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 24-10-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Dropshipping\Portfolio\Hydrators;

use App\Actions\OrgAction;
use App\Actions\Traits\WithEnumStats;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Models\CRM\Customer;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydratePortfolios extends OrgAction
{
    use AsAction;
    use WithEnumStats;


    protected Customer $customer;

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
            'number_customer_clients'                  => $customer->clients()->count(),
            'number_current_customer_clients'          => $customer->clients()->where('status', true)->count(),
            'number_portfolios'                        => $customer->portfolios()->count(),
            'number_current_portfolios'                => $customer->portfolios()->where('status', true)->count(),
            'number_products'                          => $customer->products()->count(),
            'number_current_products'                  => $customer->products()->where('status', true)->count(),
        ];

        foreach (ProductStateEnum::cases() as $case) {
            $stats['number_products_state_'.$case->snake()] = $customer->products()->where('state', $case->value)->count();
        }

        $customer->dropshippingStats()->updateOrCreate($stats);
    }
}

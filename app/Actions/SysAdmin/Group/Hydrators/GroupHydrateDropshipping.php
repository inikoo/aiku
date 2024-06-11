<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 20 Apr 2024 22:38:08 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Models\SysAdmin\Group;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateDropshipping
{
    use AsAction;

    private Group $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->group->id))->dontRelease()];
    }

    public function handle(Group $group): void
    {
        $stats = [
            'number_customer_clients'                            => $group->clients()->count(),
            'number_current_customer_clients'                    => $group->clients()->where('status', true)->count(),
            'number_dropshipping_customer_portfolios'            => $group->dropshippingCustomerPortfolios()->count(),
            'number_current_dropshipping_customer_portfolios'    => $group->dropshippingCustomerPortfolios()->where('status', true)->count(),
            'number_products'                                    => $group->products()->count(),
            'number_current_products'                            => $group->products()->where('status', true)->count(),
        ];

        foreach (ProductStateEnum::cases() as $case) {
            $stats['number_products_state_'.$case->snake()] = $group->products()->where('state', $case->value)->count();
        }

        $group->dropshippingStats()->update($stats);
    }


}

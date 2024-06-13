<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 20 Apr 2024 22:38:08 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Models\SysAdmin\Organisation;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateCustomerClients
{
    use AsAction;

    private Organisation $organisation;

    public function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->organisation->id))->dontRelease()];
    }

    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_customer_clients'                            => $organisation->clients()->count(),
            'number_current_customer_clients'                    => $organisation->clients()->where('status', true)->count()
        ];

        $organisation->dropshippingStats()->update($stats);
    }


}

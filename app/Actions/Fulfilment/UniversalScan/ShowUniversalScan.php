<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\UniversalScan;

use App\Actions\CRM\Customer\UI\GetCustomerShowcase;
use App\Actions\Fulfilment\FulfilmentOrder\UI\IndexFulfilmentOrders;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItems;
use App\Actions\InertiaAction;
use App\Actions\Mail\DispatchedEmail\IndexDispatchedEmails;
use App\Actions\OrgAction;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Enums\UI\CustomerFulfilmentTabsEnum;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Http\Resources\Sales\OrderResource;
use App\Http\Resources\UniversalSearch\UniversalSearchResource;
use App\Models\CRM\Customer;
use App\Models\Search\UniversalSearch;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowUniversalScan extends OrgAction
{
    public function handle(string $ulid): UniversalSearch
    {
        return UniversalSearch::where('ulid', $ulid)->firstOrFail();
    }

    public function asController(Organisation $organisation, string $ulid, ActionRequest $request): UniversalSearch
    {
        $this->initialisation($organisation, $request);

        return $this->handle($ulid);
    }

    public function jsonResponse(UniversalSearch $universalSearch): JsonResource
    {
        return new UniversalSearchResource($universalSearch);
    }
}

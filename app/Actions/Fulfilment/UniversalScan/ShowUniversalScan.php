<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\UniversalScan;

use App\Actions\OrgAction;
use App\Http\Resources\UniversalSearch\UniversalSearchResource;
use App\Models\Helpers\UniversalSearch;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;
use Lorisleiva\Actions\ActionRequest;

class ShowUniversalScan extends OrgAction
{
    public function handle(string $ulid): UniversalSearch
    {
        return UniversalSearch::where('slug', $ulid)->firstOrFail();
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, string $ulid, ActionRequest $request): UniversalSearch
    {
        $this->initialisation($organisation, $request);

        return $this->handle($ulid);
    }

    public function jsonResponse(UniversalSearch $universalSearch): JsonResource
    {
        return new UniversalSearchResource($universalSearch);
    }
}

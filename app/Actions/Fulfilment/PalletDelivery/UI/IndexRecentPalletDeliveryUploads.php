<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Jan 2025 14:37:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\UI;

use App\Actions\OrgAction;
use App\Http\Resources\Helpers\UploadsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Helpers\Upload;
use App\Models\SysAdmin\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class IndexRecentPalletDeliveryUploads extends OrgAction
{
    use AsAction;
    use WithAttributes;

    private Fulfilment $parent;

    public function handle(PalletDelivery $palletDelivery, User $user): array|Collection
    {
        $upload = Upload::where('user_id', $user->id)->where('parent_id', $palletDelivery->id)->where('parent_type', $palletDelivery->getMorphClass());
        // where created_at > last 24 hrs

        return $upload->orderBy('date', 'DESC')->get()->reverse();
    }

    public function jsonResponse(Collection $collection): JsonResource
    {
        return UploadsResource::collection($collection);
    }

    public function asController(PalletDelivery $palletDelivery, ActionRequest $request): array|Collection
    {

        $this->parent = $palletDelivery->fulfilment; // Needed for authorisation
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $request);

        return $this->handle($palletDelivery, $request->user());


    }




}

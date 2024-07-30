<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 16 Aug 2023 08:09:28 Malaysia Time, Pantai Lembeng, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Uploads;

use App\Http\Resources\Helpers\UploadsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Helpers\Upload;
use App\Models\SysAdmin\Organisation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class HistoryUploads
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    public function handle(string $class, $argument): array|Collection
    {
        $upload = Upload::whereType($class);
        if(!blank($argument)) {
            $upload->where(Arr::get($argument, 'key'), Arr::get($argument, 'value'));
        }

        $uploads = $upload->orderBy('id', 'DESC')->limit(4)->get()->reverse();

        if ($uploads->isEmpty()) {
            return [
                'message' => 'No uploads found.'
            ];
        }

        return $uploads;
    }

    public function jsonResponse(Collection $collection): JsonResource
    {
        return UploadsResource::collection($collection);
    }

    public function inPalletDelivery(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, PalletDelivery $palletDelivery, ActionRequest $request): array|Collection
    {
        return $this->handle(class_basename(Pallet::class), [
            'key'   => 'user_id',
            'value' => $request->user()->id
        ]);
    }

    public function inPalletReturn(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, PalletReturn $palletReturn, ActionRequest $request): array|Collection
    {
        return $this->handle(class_basename(Pallet::class), [
            'key'   => 'user_id',
            'value' => $request->user()->id
        ]);
    }
    public function inPalletRetina(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, PalletDelivery $palletDelivery, ActionRequest $request): array|Collection
    {
        return $this->handle(class_basename(Pallet::class), [
            'key'   => 'user_id',
            'value' => $request->user()->id
        ]);
    }
    public function inPalletReturnRetina(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, PalletReturn $palletReturn, ActionRequest $request): array|Collection
    {
        return $this->handle(class_basename(Pallet::class), [
            'key'   => 'user_id',
            'value' => $request->user()->id
        ]);
    }


}

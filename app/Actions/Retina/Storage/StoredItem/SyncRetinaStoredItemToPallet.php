<?php
/*
 * author Arya Permana - Kirin
 * created on 17-01-2025-09h-08m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\Storage\StoredItem;

use App\Actions\OrgAction;
use App\Actions\RetinaAction;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SyncRetinaStoredItemToPallet extends RetinaAction
{
    use AsAction;
    use WithAttributes;

    protected FulfilmentCustomer $fulfilmentCustomer;
    protected Fulfilment $fulfilment;

    public function handle(Pallet $pallet, array $modelData): void
    {
        Arr::map(Arr::get($modelData, 'stored_item_ids'), function (array $item, int|string $key) {
            if ($key == 'null') {
                throw ValidationException::withMessages(['stored_item_ids' => __('The stored item is required')]);
            }
        });

        $pallet->storedItems()->sync(Arr::get($modelData, 'stored_item_ids', []));
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            // TODO: Raul please do the permission for the web user
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'stored_item_ids'             => ['sometimes', 'array'],
            'stored_item_ids.*.quantity'  => ['required', 'integer', 'min:1'],
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'stored_item_ids.*.quantity.required' => __('The quantity is required'),
            'stored_item_ids.*.quantity.integer'  => __('The quantity must be an integer'),
            'stored_item_ids.*.quantity.min'      => __('The quantity must be at least 1'),
        ];
    }

    public function asController(Pallet $pallet, ActionRequest $request): void
    {
        $this->fulfilmentCustomer = $pallet->fulfilmentCustomer;
        $this->fulfilment         = $pallet->fulfilment;

        $this->initialisation($request);

        $this->handle($pallet, $this->validatedData);
    }

    public function jsonResponse(Pallet $pallet): PalletResource
    {
        return new PalletResource($pallet);
    }
}

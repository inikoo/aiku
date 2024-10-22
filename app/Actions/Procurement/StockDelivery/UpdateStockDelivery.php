<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 14:50:49 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\StockDelivery;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Procurement\StockDelivery;
use Lorisleiva\Actions\ActionRequest;

class UpdateStockDelivery extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;

    public function handle(StockDelivery $stockDelivery, array $modelData): StockDelivery
    {
        return $this->update($stockDelivery, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        $rules = [
            'reference'   => ['required', 'numeric', 'unique:stock_deliveries'],
            'date'        => ['required', 'date'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'exchange'    => ['required', 'numeric'],
            'parent_code' => ['sometimes', 'required', 'string', 'max:256'],
            'parent_name' => ['sometimes', 'required', 'string', 'max:256'],
        ];

        if (!$this->strict) {
            $rules                 = $this->noStrictStoreRules($rules);
            $rules['date']         = ['sometimes', 'date'];
            $rules['received_at']  = ['sometimes', 'nullable', 'date'];
            $rules['checked_at']   = ['sometimes', 'nullable', 'date'];
            $rules['settled_at']   = ['sometimes', 'nullable', 'date'];
            $rules['cancelled_at'] = ['sometimes', 'nullable', 'date'];
        }

        return $rules;
    }

    public function action(StockDelivery $stockDelivery, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): StockDelivery
    {
        $this->strict = $strict;
        if (!$audit) {
            StockDelivery::disableAuditing();
        }
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($stockDelivery->organisation, $modelData);

        return $this->handle($stockDelivery, $this->validatedData);
    }

    public function asController(StockDelivery $stockDelivery, ActionRequest $request): StockDelivery
    {
        $this->initialisation($stockDelivery->organisation, $request);

        return $this->handle($stockDelivery, $this->validatedData);
    }


}

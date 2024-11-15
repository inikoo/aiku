<?php
/*
 * author Arya Permana - Kirin
 * created on 14-11-2024-14h-40m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Goods\Ingredient;

use App\Actions\GrpAction;
use App\Models\Catalogue\Product;
use App\Models\Goods\TradeUnit;
use App\Models\SupplyChain\Stock;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SyncIngredientModel extends GrpAction
{
    use AsAction;
    use WithAttributes;

    public function handle(Product|Stock|TradeUnit $model, array $modelData): void
    {
        $model->ingredients()->sync(Arr::get($modelData, 'ingredients', []));
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'ingredients'            => ['sometimes', 'array'],
        ];
    }

    public function asController(Product|Stock|TradeUnit $model, ActionRequest $request): void
    {
        $this->initialisation($model->group, $request);

        $this->handle($model, $this->validatedData);
    }
}

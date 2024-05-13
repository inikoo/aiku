<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 08:23:57 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Query;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateQueries;
use App\Models\Helpers\Query;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreQuery
{
    use AsAction;
    use WithAttributes;
    use WithQueryCompiler;

    private bool $asAction = false;

    /**
     * @throws \Exception
     */
    public function handle(array $modelData): Query
    {
        data_set($modelData, 'compiled_constrains', $this->compileConstrains($modelData['constrains']));

        $modelData['has_arguments'] = false;
        if(count(Arr::get($modelData, 'compiled_constrains.arguments', []))) {
            $modelData['has_arguments'] = true;
        }


        /** @var Query $query */
        $query = Query::create($modelData);
        if ($query->parent_type == 'Shop') {
            ShopHydrateQueries::dispatch($query->parent);
        }

        return $query;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.prospects.edit");
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'parent_type' => ['required', 'string'],
            'parent_id'   => ['required', 'integer'],
            'model_type'  => ['required', 'string'],
            'constrains'  => ['required', 'array'],
            'is_seeded'   => ['sometimes', 'boolean'],
            'slug'        => ['sometimes', 'string'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function action(array $objectData): Query
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($validatedData);
    }

    /**
     * @throws \Exception
     */
    public function asController(ActionRequest $request): Query
    {
        $request->validate();

        return $this->handle($request->validated());
    }
}

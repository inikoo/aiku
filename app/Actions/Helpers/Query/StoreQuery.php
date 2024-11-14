<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 08:23:57 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Query;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateQueries;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Catalogue\Shop;
use App\Models\Helpers\Query;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class StoreQuery extends OrgAction
{
    use WithQueryCompiler;
    use WithNoStrictRules;


    private Organisation|Shop $parent;

    private mixed $model;


    /**
     * @throws \Exception
     */
    public function handle(Organisation|Shop $parent, array $modelData): Query
    {
        data_set($modelData, 'group_id', $this->organisation->group_id);
        data_set($modelData, 'organisation_id', $this->organisation->id);
        data_set($modelData, 'compiled_constrains', $this->compileConstrains($modelData['constrains']));

        $modelData['has_arguments'] = false;
        if (count(Arr::get($modelData, 'compiled_constrains.arguments', []))) {
            $modelData['has_arguments'] = true;
        }

        /** @var Query $query */
        $query = $parent->queries()->create($modelData);


        if ($parent instanceof Shop) {
            ShopHydrateQueries::dispatch($query->parent)->delay($this->hydratorsDelay);
        }

        return $query;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        $rules = [
            'name'       => [
                'required',
                'max:255',
                'string',
                new IUnique(
                    table: 'queries',
                    extraConditions: $this->parent instanceof Organisation
                        ? [
                            [
                                'column' => 'organisation_id',
                                'value'  => $this->parent->id
                            ],
                            [
                                'column' => 'model',
                                'value'  => $this->model
                            ]
                        ]
                        : [
                            [
                                'column' => 'shop_id',
                                'value'  => $this->parent->id
                            ],
                            [
                                'column' => 'model',
                                'value'  => $this->model
                            ]
                        ],
                ),
            ],
            'model'      => ['required', 'string'],
            'constrains' => ['required', 'array'],
            'seed_code'  => ['sometimes', 'string'],
            'is_static'  => ['sometimes', 'boolean'],
        ];

        if (!$this->strict) {
            $rules['source_constrains'] = ['required', 'array'];
            $rules['constrains'] = ['sometimes', 'nullable', 'array'];
            $rules               = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    /**
     * @throws \Exception
     */
    public function action(Organisation|Shop $parent, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): Query
    {
        if (!$audit) {
            Query::disableAuditing();
        }
        $this->parent = $parent;
        $this->model  = Arr::get($modelData, 'model', 'Error');


        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;

        if (class_basename($parent) == 'Shop') {
            $this->initialisationFromShop($parent, $modelData);
        } else {
            $this->initialisation($parent, $modelData);
        }

        return $this->handle($parent, $this->validatedData);
    }


}

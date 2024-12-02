<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 08:23:57 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Query;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Helpers\Query;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateQuery extends OrgAction
{
    use WithActionUpdate;
    use WithQueryCompiler;
    use WithNoStrictRules;


    private Query $query;

    /**
     * @throws \Exception
     */
    public function handle(Query $query, array $modelData): Query
    {
        if (Arr::exists($modelData, 'constrains')) {
            data_set($modelData, 'compiled_constrains', $this->compileConstrains($modelData['constrains']));
        }

        return $this->update($query, $modelData);
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
            'code'       => [
                'sometimes',
                'required',
                'max:255',
                'string',
                new IUnique(
                    table: 'queries',
                    extraConditions: [
                        $this->query->shop->id
                            ? [
                            [
                                'column' => 'organisation_id',
                                'value'  => $this->query->organisation_id
                            ]
                        ]
                            : [
                            [
                                'column' => 'shop_id',
                                'value'  => $this->query->shop_id
                            ]
                        ],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->query->id
                        ]
                    ]
                ),
            ],
            'constrains' => ['sometimes', 'array'],
            'is_static'  => ['sometimes', 'boolean'],
        ];

        if (!$this->strict) {
            $rules['source_constrains'] = ['sometimes', 'required', 'array'];
            $rules                      = $this->noStrictUpdateRules($rules);
        }

        return $rules;
    }

    public function action(Query $query, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): Query
    {
        $this->strict = $strict;
        if (!$audit) {
            Query::disableAuditing();
        }
        $this->asAction       = true;
        $this->query          = $query;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($query->organisation, $modelData);

        return $this->handle($query, $this->validatedData);
    }


}

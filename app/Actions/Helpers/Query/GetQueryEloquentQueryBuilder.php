<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Nov 2023 18:40:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Query;

use App\Models\CRM\Prospect;
use App\Models\Helpers\Query;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsObject;
use Spatie\QueryBuilder\QueryBuilder;

class GetQueryEloquentQueryBuilder
{
    use AsObject;

    /**
     * @throws \Exception
     */
    public function handle(Query $query, ?array $customArguments = null): QueryBuilder
    {
        if ($query->model_type == 'Prospect') {
            $class = Prospect::class;
        } else {
            throw new \Exception('Unknown model type: '.$query->model_type);
        }

        return $this->buildQuery(
            $class,
            $query->parent,
            $customArguments ?: $query->compiled_constrains
        );
    }


    public function buildQuery($model, $parent, array $compiledConstrains): QueryBuilder
    {


        $queryBuilder = QueryBuilder::for($model);
        if (Arr::get($compiledConstrains, 'returnZero', false)) {
            return $queryBuilder->whereRaw('0=1');
        }


        if (class_basename($parent) == 'Shop') {
            $queryBuilder->where('shop_id', $parent->id);
        }

        foreach (Arr::get($compiledConstrains, 'joins', []) as $join) {
            switch ($join['type']) {
                case 'left':
                    $queryBuilder->leftJoin($join['table'], $join['first'], $join['operator'], $join['second']);
                    break;
                case 'right':
                    $queryBuilder->rightJoin($join['table'], $join['first'], $join['operator'], $join['second']);
                    break;
                case 'inner':
                    $queryBuilder->join($join['table'], $join['first'], $join['operator'], $join['second']);
                    break;
            }
        }



        foreach (Arr::get($compiledConstrains, 'constrains') as $constrainData) {
            $constrainType = $constrainData['type'];

            if ($constrainType == 'false') {
                $queryBuilder->where(true, false);
            } elseif ($constrainType == 'group') {
                $subConstrainData = $constrainData['parameters'];
                $queryBuilder
                    ->where(
                        function (Builder $subQueryBuilder) use ($subConstrainData, $compiledConstrains) {
                            foreach ($subConstrainData as $constrainData) {
                                $constrainType = $constrainData['type'];

                                $subQueryBuilder = $this->addConstrain($subQueryBuilder, $constrainType, $constrainData['parameters'], $compiledConstrains);
                            }
                        }
                    );
            } elseif ($constrainType == 'orGroup') {
                $subConstrainData = $constrainData['parameters'];
                $queryBuilder
                    ->where(
                        function (Builder $subQueryBuilder) use ($subConstrainData, $compiledConstrains) {
                            foreach ($subConstrainData as $constrainData) {
                                $constrainType   = $constrainData['type'];
                                $subQueryBuilder = $this->addConstrain($subQueryBuilder, $constrainType, $constrainData['parameters'], $compiledConstrains);
                            }
                        }
                    );
            } else {
                $queryBuilder = $this->addConstrain($queryBuilder, $constrainType, $constrainData['parameters'], $compiledConstrains);
            }
        }


        return $queryBuilder;
    }


    protected function addConstrain($queryBuilder, $constrainType, $constrainData, $compiledConstrains)
    {
        if ($constrainType == 'with') {
            if (is_array($constrainData)) {
                foreach ($constrainData as $constrain) {
                    $queryBuilder->whereNotNull($constrain);
                }
            } else {
                $queryBuilder->whereNotNull($constrainData);
            }
        } elseif ($constrainType == 'without') {
            $queryBuilder->whereNull($constrainData);
        } elseif ($constrainType == 'where') {
            $value = $constrainData[2];
            if (preg_match('/^__.+__$/', $value)) {
                $value = $this->getArgument(Arr::get($compiledConstrains['arguments'], $value));
            }
            $queryBuilder->where($constrainData[0], $constrainData[1], $value);
        } elseif ($constrainType == 'orWhere') {
            $value = $constrainData[2];
            if (preg_match('/^__.+__$/', $value)) {
                $value = $this->getArgument(Arr::get($compiledConstrains['arguments'], $value));
            }
            $queryBuilder->orWhere($constrainData[0], $constrainData[1], $value);
        } elseif ($constrainType == 'whereIn') {
            $queryBuilder->whereIn($constrainData[0], $constrainData[1]);
        } elseif ($constrainType == 'orWhereNull') {
            $queryBuilder->orWhereNull($constrainData);
        } elseif ($constrainType == 'Group') {
            $queryBuilder
                ->where(
                    function (Builder $subQueryBuilder) use ($constrainData, $compiledConstrains) {
                        foreach ($constrainData as $subConstrainData) {
                            $subQueryBuilder = $this->addConstrain($subQueryBuilder, $subConstrainData['type'], $subConstrainData['parameters'], $compiledConstrains);
                        }
                    }
                );
        } elseif ($constrainType == 'orGroup') {
            $queryBuilder
                ->orWhere(
                    function (Builder $subQueryBuilder) use ($constrainData, $compiledConstrains) {
                        foreach ($constrainData as $subConstrainData) {
                            $subQueryBuilder = $this->addConstrain($subQueryBuilder, $subConstrainData['type'], $subConstrainData['parameters'], $compiledConstrains);
                        }
                    }
                );
        } elseif ($constrainType == 'filter') {
            if (Arr::get($constrainData, 'all')) {
                $queryBuilder->withAllTags($constrainData['all'], 'crm');
            } elseif (Arr::get($constrainData, 'any')) {
                $queryBuilder->withAnyTags($constrainData['any'], 'crm');
            }
        }

        return $queryBuilder;
    }

    protected function getArgument($argumentData): ?\DateTime
    {
        if (!$argumentData) {
            return null;
        }

        $value = null;
        if (Arr::get($argumentData, 'type') == 'dateSubtraction') {
            $date = Carbon::now();
            $date->sub(Arr::get($argumentData, 'value.quantity'), Arr::get($argumentData, 'value.unit'));
            $value = $date->toDateTime();
        }


        return $value;
    }

}

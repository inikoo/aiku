<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Jan 2024 19:26:03 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Services;

class QueryBuilder extends \Spatie\QueryBuilder\QueryBuilder
{
    public function whereElementGroup(
        string $key,
        array $allowedElements,
        callable $engine,
        ?string $prefix = null
    ): self {
        $elementsData = null;

        $argumentName = ($prefix ? $prefix.'_' : '').'elements';


        if (request()->has("$argumentName.$key")) {
            $elements               = explode(',', request()->input("$argumentName.$key"));
            $validatedElements      = array_intersect($allowedElements, $elements);
            $countValidatedElements = count($validatedElements);
            if ($countValidatedElements > 0 and $countValidatedElements < count($allowedElements)) {
                $elementsData = $validatedElements;
            }
        }


        if ($elementsData) {
            $engine($this, $elementsData);
        }

        return $this;
    }

    public function withPaginator($prefix): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $perPage = config('ui.table.records_per_page');

        $argumentName = ($prefix ? $prefix.'_' : '').'perPage';
        if (request()->has($argumentName)) {
            $inputtedPerPage = (int)request()->input($argumentName);

            if ($inputtedPerPage < 10) {
                $perPage = 10;
            } elseif ($inputtedPerPage > 1000) {
                $perPage = 1000;
            } else {
                $perPage = $inputtedPerPage;
            }
        }


        return $this->paginate(
            perPage: $perPage,
            pageName: $prefix ? $prefix.'Page' : 'page'
        );
    }


}

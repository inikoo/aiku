<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Jun 2023 22:45:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Providers;

use App\InertiaTable\InertiaTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Response;
use Inertia\Response as InertiaResponse;

class MacroServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }


    public function boot(): void
    {
        Str::macro('possessive', function (string $string): string {
            return $string.'\''.(
                Str::endsWith($string, ['s', 'S']) ? '' : 's'
            );
        });

        InertiaResponse::macro('getQueryBuilderProps', function (): array {
            return $this->props['queryBuilderProps'] ?? [];
        });

        InertiaResponse::macro('table', function (callable $withTableBuilder = null): Response {
            $tableBuilder = new InertiaTable(request());

            if ($withTableBuilder) {
                $withTableBuilder($tableBuilder);
            }

            return $tableBuilder->applyTo($this);
        });

        Builder::macro('whereAnyWordStartWith', function (string $column, string $value): Builder {
            $quotedValue = DB::connection()->getPdo()->quote($value);

            return $this->where(DB::raw("extensions.remove_accents(".$column.")  COLLATE \"C\""), '~*', DB::raw("('\y' ||  extensions.remove_accents($quotedValue) ||   '.*\y')"));
        });
        Builder::macro('whereStartWith', function (string $column, string $value): Builder {
            return $this->whereRaw("$column COLLATE \"C\" ILIKE ?", $value.'%');
        });


        Builder::macro('whereWith', function (string $column, string $value): Builder {
            return $this->whereRaw("$column COLLATE \"C\" ILIKE ?", "%".$value.'%');
        });

        Builder::macro('orWhereAnyWordStartWith', function (string $column, string $value): Builder {
            $quotedValue = DB::connection()->getPdo()->quote($value);

            return $this->orWhere(DB::raw("extensions.remove_accents(".$column.")  COLLATE \"C\""), '~*', DB::raw("('\y' ||  extensions.remove_accents($quotedValue) ||   '.*\y')"));
        });

        Builder::macro('orWhereStartWith', function (string $column, string $value): Builder {
            return $this->orWhereRaw("$column COLLATE \"C\" ILIKE ?", $value.'%');
        });

        Builder::macro('orWhereWith', function (string $column, string $value): Builder {
            return $this->orWhereRaw("$column COLLATE \"C\" ILIKE ?", "%".$value.'%');
        });




        Builder::macro('whereElementGroup', function (string $key, array $allowedElements, callable $engine, ?string $prefix = null): Builder {
            $elementsData = null;

            $argumentName = ($prefix ? $prefix.'_' : '').'elements';


            if (request()->has("$argumentName.$key")) {
                $elements = explode(',', request()->input("$argumentName.$key"));


                $validatedElements = array_intersect($allowedElements, $elements);


                $countValidatedElements = count($validatedElements);
                if ($countValidatedElements > 0 and $countValidatedElements < count($allowedElements)) {
                    $elementsData = $validatedElements;
                }
            }


            if ($elementsData) {
                $engine($this, $elementsData);
            }

            return $this;
        });

        Builder::macro('withPaginator', function ($prefix) {
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
        });


        Request::macro('validatedShiftToArray', function ($map = []): array {
            /** @noinspection PhpUndefinedMethodInspection */
            $validated = $this->validated();
            foreach ($map as $field => $destination) {
                if (array_key_exists($field, $validated)) {
                    Arr::set($validated, "$destination.$field", $validated[$field]);
                    Arr::forget($validated, $field);
                }
            }

            return $validated;
        });

        if (!Collection::hasMacro('paginate')) {
            Collection::macro(
                'paginate',
                function ($perPage = 15, $page = null, $options = []) {
                    $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

                    return (new LengthAwarePaginator(
                        $this->forPage($page, $perPage)->values()->all(),
                        $this->count(),
                        $perPage,
                        $page,
                        $options
                    ))
                        ->withPath('');
                }
            );
        }
    }
}

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
            $quotedValue=DB::connection()->getPdo()->quote($value);
            return $this->where(DB::raw("extensions.remove_accents(". $column.")"), '~*', DB::raw("('\y' ||  extensions.remove_accents($quotedValue) ||   '.*\y')"));
        });

        Builder::macro('elements', function (array $blueprint, callable $elementBuilder): Builder {

            $elementsData=[];
            if(request()->has('elements')) {



                $validKeys=array_keys($blueprint);
                $elements =request()->get('elements');
                foreach($elements as $key=>$values) {
                    if(in_array($key, $validKeys)) {

                        $validatedValues     =array_intersect($blueprint[$key], explode(',', $values));
                        $countValidatedValues=count($validatedValues);
                        if($countValidatedValues>0 and $countValidatedValues<count($blueprint[$key])) {
                            $elementsData[$key]=$validatedValues;
                        }

                    }
                }


            }

            if(count($elementsData)) {
                $elementBuilder($this, $elementsData);
            }
            return $this;
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

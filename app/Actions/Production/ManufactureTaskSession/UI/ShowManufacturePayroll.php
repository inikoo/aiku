<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 07 Sep 2026 12:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\ManufactureTaskSession\UI;

use App\Actions\OrgAction;
use App\Actions\Production\Production\UI\ShowArtisansDashboard;
use App\Models\Production\Production;
use App\Actions\Production\ManufactureTaskSession\GetManufacturePayroll;
use App\InertiaTable\InertiaTable;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowManufacturePayroll extends OrgAction
{
    private Carbon $from;
    private Carbon $to;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(Production $production, Carbon $from, Carbon $to): array
    {
        return array_map(fn (array $artisan) => Arr::except($artisan, 'lines'), GetManufacturePayroll::run($production, $from, $to));
    }

    public function rules(): array
    {
        return [
            'from' => ['sometimes', 'date'],
            'to'   => ['sometimes', 'date', 'after_or_equal:from'],
        ];
    }

    /**
     * Last week, Monday to Sunday, unless a period is asked for.
     */
    public static function period(array $modelData): array
    {
        return [
            Carbon::parse(Arr::get($modelData, 'from', now()->subWeek()->startOfWeek()->toDateString())),
            Carbon::parse(Arr::get($modelData, 'to', now()->subWeek()->endOfWeek()->toDateString())),
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return ShowArtisansDashboard::canSeeArtisanPay($request->user(), $this->production);
    }

    public function asController(Organisation $organisation, Production $production, ActionRequest $request): array
    {
        $this->initialisationFromProduction($production, $request);
        [$this->from, $this->to] = self::period($this->validatedData);

        return $this->handle($production, $this->from, $this->to);
    }

    public function htmlResponse(array $artisans, ActionRequest $request): Response
    {
        $routeParameters = Arr::only($request->route()->originalParameters(), ['organisation', 'production']);
        $period          = ['from' => $this->from->toDateString(), 'to' => $this->to->toDateString()];

        return Inertia::render(
            'Org/Production/ManufacturePayroll',
            [
                'title'       => __('Payroll'),
                'breadcrumbs' => $this->getBreadcrumbs($routeParameters),
                'pageHead'    => [
                    'title' => __('Payroll'),
                    'icon'  => [
                        'icon'  => ['fal', 'fa-money-check-alt'],
                        'title' => __('Payroll'),
                    ],
                ],
                'period'               => $period,
                'payroll_export_route' => [
                    'name'       => 'grp.org.productions.show.artisans.payroll.export',
                    'parameters' => $routeParameters
                ],
                'data'                 => JsonResource::collection(new LengthAwarePaginator(
                    array_map(fn (array $artisan) => $artisan + [
                        'route' => [
                            'name'       => 'grp.org.productions.show.artisans.payroll.show',
                            'parameters' => $routeParameters + ['user' => $artisan['user_id']] + $period,
                        ],
                    ], $artisans),
                    count($artisans),
                    max(1, count($artisans))
                )),
            ]
        )->table($this->tableStructure(GetManufacturePayroll::make()->grandTotal($artisans)));
    }

    public static function payColumns(): array
    {
        return [
            'working' => __('Working hours'),
            'band_0'  => __('Band 0'),
            'band_1'  => __('Band 1'),
            'band_2'  => __('Band 2'),
            'band_3'  => __('Band 3'),
            'band_d'  => __('Development'),
            'band_dg' => __('Guru Dev'),
            'guru'    => __('Guru bonus'),
        ];
    }

    public function tableStructure(array $grandTotal): Closure
    {
        return function (InertiaTable $table) use ($grandTotal) {
            $table
                ->withFooterRows(['data' => [$grandTotal]])
                ->withEmptyState(['title' => __('No work recorded in this period')])
                ->column(key: 'worker', label: __('Worker'), canBeHidden: false);

            foreach (self::payColumns() as $key => $label) {
                $table->column(key: $key, label: $label, canBeHidden: false, align: 'right');
            }

            $table->column(key: 'total', label: __('Total'), canBeHidden: false, type: 'number');
        };
    }

    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        $routeParameters = Arr::only($routeParameters, ['organisation', 'production']);

        return array_merge(
            (new ShowArtisansDashboard())->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.productions.show.artisans.payroll',
                            'parameters' => $routeParameters,
                        ],
                        'label' => __('Payroll'),
                    ],
                    'suffix' => $suffix,
                ],
            ]
        );
    }
}

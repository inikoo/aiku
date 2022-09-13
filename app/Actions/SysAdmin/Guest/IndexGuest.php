<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 14 Sept 2022 00:56:10 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest;

use App\Actions\SysAdmin\ShowSysAdminDashboard;
use App\Actions\UI\WithInertia;
use App\Http\Resources\SysAdmin\GuestInertiaResource;
use App\Http\Resources\SysAdmin\GuestResource;
use App\Models\Organisations\Organisation;
use App\Models\SysAdmin\Guest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class IndexGuest
{
    use AsAction;
    use WithInertia;


    public function handle(Organisation $organisation): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('guest.name', 'LIKE', "%$value%")
                ->orWhere('guest.code', 'LIKE', "%$value%");
            });
        });


        return QueryBuilder::for(Guest::class)
            ->where('organisation_id', $organisation->id)
            ->defaultSort('code')
            ->select(['id', 'code', 'name',])
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? 15)
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('sysadmin.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $guests): AnonymousResourceCollection
    {
        return GuestResource::collection($guests);
    }


    public function htmlResponse(LengthAwarePaginator $guests)
    {
        return Inertia::render(
            'SysAdmin/Guests',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('guests'),
                'pageHead'    => [
                    'title' => __('guests'),
                ],
                'guests'       => GuestInertiaResource::collection($guests),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        });
    }


    public function asController(Request $request): LengthAwarePaginator
    {
        $this->fillFromRequest($request);

        return $this->handle($request->user()->currentUiOrganisation);
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new ShowSysAdminDashboard())->getBreadcrumbs(),
            [
                'sysadmin.guests.index' => [
                    'route' => 'sysadmin.guests.index',
                    'modelLabel' => [
                        'label' => __('guests')
                    ],
                ],
            ]
        );
    }


}

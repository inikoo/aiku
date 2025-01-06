<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:08:59 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\DispatchedEmail\UI;

use App\Actions\Comms\PostRoom\UI\ShowPostRoom;
use App\Actions\OrgAction;
use App\Actions\Overview\ShowGroupOverviewHub;
use App\Actions\UI\Marketing\MarketingHub;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\Comms\DispatchedEmail;
use App\Models\Comms\Mailshot;
use App\Models\Comms\Outbox;
use App\Models\Comms\PostRoom;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexDispatchedEmails extends OrgAction
{
    private Group|Organisation|Shop $parent;

    public function handle(Group|Mailshot|Outbox|PostRoom|Organisation|Shop $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('dispatched_emails.state', '~*', "\y$value\y")
                    ->orWhere('dispatched_emails.number_reads', '=', $value)
                    ->orWhere('dispatched_emails.number_clicks', '=', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(DispatchedEmail::class);
        $queryBuilder->leftJoin('organisations', 'dispatched_emails.organisation_id', '=', 'organisations.id')
            ->leftJoin('shops', 'dispatched_emails.shop_id', '=', 'shops.id');

        if (is_array($this->elementGroups) || is_object($this->elementGroups) && !($parent instanceof Group)) {
            foreach ($this->elementGroups as $key => $elementGroup) {
                $queryBuilder->whereElementGroup(
                    key: $key,
                    allowedElements: array_keys($elementGroup['elements']),
                    engine: $elementGroup['engine'],
                    prefix: $prefix
                );
            }
        }

        return $queryBuilder
            ->defaultSort('dispatched_emails.state')
            ->select(['dispatched_emails.state',
                'dispatched_emails.id',
                'dispatched_emails.number_reads',
                'dispatched_emails.number_clicks',
                'outboxes.slug as outbox_id',
                'outboxes.slug as outboxes_id',
                'shops.name as shop_name',
                'shops.slug as shop_slug',
                'organisations.name as organisation_name',
                'organisations.slug as organisation_slug',
            ])
            ->leftJoin('outboxes', 'outboxes.id', 'dispatched_emails.outbox_id')
            ->leftJoin('post_rooms', 'outboxes.post_room_id', 'post_rooms.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Comms' || class_basename($parent) == 'PostRoom') {
                    $query->where('outboxes.post_room_id', $parent->id);
                }
                if (class_basename($parent) == 'Outbox') {
                    $query->where('dispatched_emails.post_room_id', $parent->id);
                }
                if (class_basename($parent) == 'Mailshot') {
                    $query->where('dispatched_emails.mailshot_id', $parent->id);
                }
                if (class_basename($parent) == 'Group') {
                    $query->where('dispatched_emails.group_id', $parent->id);
                }
            })
            ->allowedSorts(['dispatched_emails.state', 'dispatched_emails.number_reads', 'dispatched_emails.number_clicks'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix) {

            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->column(key: 'state', label: '', type: 'icon', canBeHidden: false);
            if ($parent instanceof Group) {
                $table->column(key: 'organisation_name', label: __('organisation'), canBeHidden: false, sortable: true, searchable: true)
                    ->column(key: 'shop_name', label: __('shop'), canBeHidden: false, sortable: true, searchable: true);
            }
            $table->column(key: 'number_reads', label: __('reads'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_clicks', label: __('clicks'), canBeHidden: false, sortable: true, searchable: true);
        };
    }
    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Group) {
            return $request->user()->hasPermissionTo("group-overview");
        }
        $this->canEdit = $request->user()->hasPermissionTo('mail.edit');
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('mail.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $dispatchedEmails): AnonymousResourceCollection
    {
        return DispatchedEmailResource::collection($dispatchedEmails);
    }


    public function htmlResponse(LengthAwarePaginator $dispatched_emails, ActionRequest $request): Response
    {
        return Inertia::render(
            'Comms/DispatchedEmails',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('dispatched emails'),
                'pageHead'    => [
                    'title' => __('dispatched emails'),
                    'icon'  => ['fal', 'fa-paper-plane'],
                ],
                ...array_merge(
                    ($this->parent instanceof Group) ?
                    ['data' => DispatchedEmailResource::collection($dispatched_emails)] :
                    ['dispatched_emails' => DispatchedEmailResource::collection($dispatched_emails)]
                ),
            ]
        )->table($this->tableStructure($this->parent));
    }

    public function inGroup(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = group();
        $this->initialisationFromGroup($this->parent, $request);

        return $this->handle(parent: $this->parent);
    }

    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);
        return $this->handle($organisation);
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);
        return $this->handle($shop);
    }

    /** @noinspection PhpUnused */
    public function inPostRoomInShop(Outbox $outbox, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($outbox);
    }


    public function inPostRoomInOutboxInShop(PostRoom $postRoom, Outbox $outbox, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($outbox);
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Dispatched Emails'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'mail.dispatched-emails.index' =>
            array_merge(
                (new MarketingHub())->getBreadcrumbs(
                    $routeName,
                    $routeParameters
                ),
                $headCrumb()
            ),
            'mail.post_rooms.show.dispatched-emails.index' =>
            array_merge(
                (new ShowPostRoom())->getBreadcrumbs(),
                $headCrumb([])
            ),
            'grp.overview.comms-marketing.dispatched-emails.index' =>
            array_merge(
                ShowGroupOverviewHub::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}

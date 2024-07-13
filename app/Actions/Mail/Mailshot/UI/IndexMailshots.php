<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 22:36:41 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot\UI;

use App\Actions\OrgAction;
use App\Http\Resources\Mail\MailshotResource;
use App\InertiaTable\InertiaTable;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use App\Models\Mail\PostRoom;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexMailshots extends OrgAction
{
    use HasUIMailshots;


    public function handle(Outbox|PostRoom|Organisation $parent, $prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('mailshots.state', '~*', "\y$value\y")
                    ->orWhere('mailshots.data', '=', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder=QueryBuilder::for(Mailshot::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        return $queryBuilder
            ->defaultSort('mailshots.state')
            ->select([
                'mailshots.state',
                'mailshots.id',
                'mailshots.data',
                'outboxes.slug as outboxes_slug',
                'post_rooms.id as post_room_id'
            ])
            ->leftJoin('outboxes', 'mailshots.outbox_id', 'outboxes.id')
            ->leftJoin('post_rooms', 'outboxes.post_room_id', 'post_rooms.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Mail') {
                    $query->where('mailshots.post_room_id', $parent->id);
                }
            })
            ->allowedSorts(['mailshots.state', 'mailshots.data'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, ?array $modelOperations = null, $prefix=null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {

            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->column(key: 'state', label: __('state'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'data', label: __('data'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('mail.edit');
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('mail.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $mailshots): AnonymousResourceCollection
    {
        return MailshotResource::collection($mailshots);
    }


    public function htmlResponse(LengthAwarePaginator $mailshots, ActionRequest $request): Response
    {

        return Inertia::render(
            'Mail/Mailshots',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('mailshots '),
                'pageHead'    => [
                    'title'   => __('mailshots'),
                    'create'  => $this->canEdit && $request->route()->getName()=='mail.mailshots.index' ? [
                        'route' => [
                            'name'       => 'mail.mailshots.create',
                            'parameters' => array_values($request->route()->originalParameters())
                        ],
                        'label'=> __('mailshot')
                    ] : false,
                ],
                'payments' => MailshotResource::collection($mailshots),


            ]
        )->table($this->tableStructure($this->parent));
    }


    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {

        $this->initialisation($organisation, $request);
        return $this->handle($organisation);
    }

    public function inShop(PostRoom $postRoom, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($postRoom);
    }

    /** @noinspection PhpUnused */
    public function inOutbox(Outbox $outbox, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($outbox);
    }

    /** @noinspection PhpUnused */
    public function inPostRoomInShop(PostRoom $postRoom, Outbox $outbox, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($outbox);
    }


    /** @noinspection PhpUnused */
    public function inOutboxInShop(Outbox $outbox, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($outbox);
    }
}

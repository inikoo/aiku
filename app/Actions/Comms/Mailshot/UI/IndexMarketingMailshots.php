<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Mailshot\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasCatalogueAuthorisation;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Http\Resources\Mail\MailshotResource;
use App\Http\Resources\Mail\MarketingMailshotsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\Comms\Mailshot;
use App\Models\Comms\Outbox;
use App\Models\Comms\PostRoom;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexMarketingMailshots extends OrgAction
{
    use HasUIMailshots;
    use HasCatalogueAuthorisation;

    public Outbox|PostRoom|Organisation|Shop $parent;

    public function handle(Outbox|PostRoom|Organisation|Shop $parent, $prefix = null): LengthAwarePaginator
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

        $queryBuilder = QueryBuilder::for(Mailshot::class);
        $queryBuilder->leftJoin('outboxes', 'mailshots.outbox_id', 'outboxes.id')
                        ->leftJoin('mailshot_stats', 'mailshot_stats.mailshot_id', 'mailshots.id')
                        ->leftJoin('post_rooms', 'outboxes.post_room_id', 'post_rooms.id')
                        ->when($parent, function ($query) use ($parent) {
                            if (class_basename($parent) == 'Mail') {
                                $query->where('mailshots.post_room_id', $parent->id);
                            }
                        });
        $queryBuilder->where('outboxes.type', OutboxCodeEnum::MARKETING->value);
        return $queryBuilder
            ->defaultSort('mailshots.id')
            ->select([
                'mailshots.state',
                'mailshots.date',
                'mailshots.slug',
                'mailshots.id',
                'mailshots.subject',
                'outboxes.slug as outboxes_slug',
                'post_rooms.id as post_room_id',
                'mailshot_stats.number_dispatched_emails as dispatched_emails',
                'mailshot_stats.number_dispatched_emails_state_sent as sent',
                'mailshot_stats.number_dispatched_emails_state_delivered as delivered',
                'mailshot_stats.number_dispatched_emails_state_hard_bounce as hard_bounce',
                'mailshot_stats.number_dispatched_emails_state_soft_bounce as soft_bounce',
                'mailshot_stats.number_dispatched_emails_state_opened as opened',
                'mailshot_stats.number_dispatched_emails_state_clicked as clicked',
                'mailshot_stats.number_dispatched_emails_state_spam as spam',
            ])

            ->allowedSorts(['mailshots.state', 'mailshots.data'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, ?array $modelOperations = null, $prefix = null): Closure
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
                ->column(key: 'subject', label: __('subject'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'state', label: __('state'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'sent', label: __('sent'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'hard_bounce', label: __('hard bounce'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'soft_bounce', label: __('soft bounce'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'delivered', label: __('delivered'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'opened', label: __('opened'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'clicked', label: __('clicked'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'spam', label: __('spam'), canBeHidden: false, sortable: true, searchable: true);
        };
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
                    $request->route()->originalParameters(),
                    $this->parent
                ),
                'title'       => __('newsletter'),
                'pageHead'    => [
                    'title'    => __('newsletter'),
                    'actions'  => [
                        [
                            'type'    => 'button',
                            'style'   => 'create',
                            'label'   => __('mailshot'),
                            'route'   => [
                                'name'       => 'grp.org.shops.show.marketing.mailshots.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ],
                ],
                'data' => MarketingMailshotsResource::collection($mailshots),
            ]
        )->table($this->tableStructure($this->parent));
    }

    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);
        return $this->handle($organisation);
    }

    public function inShop(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($organisation);
    }
}

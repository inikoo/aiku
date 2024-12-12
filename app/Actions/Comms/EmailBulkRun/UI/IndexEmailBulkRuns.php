<?php
/*
 * author Arya Permana - Kirin
 * created on 12-12-2024-16h-41m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Comms\EmailBulkRun\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasCatalogueAuthorisation;
use App\Http\Resources\Mail\EmailBulkRunsResource;
use App\Http\Resources\Mail\MailshotResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\Comms\EmailBulkRun;
use App\Models\Comms\Mailshot;
use App\Models\Comms\OrgPostRoom;
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

class IndexEmailBulkRuns extends OrgAction
{

    private Organisation|Shop|Outbox $parent;

    public function handle(Organisation|Shop|Outbox $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('email_bulk_runs.subject', '~*', "\y$value\y")
                    ->orWhere('email_bulk_runs.data', '=', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(EmailBulkRun::class);

        if ($parent instanceof Outbox) {
            $queryBuilder->where('email_bulk_runs.outbox_id', $parent->id);
        } elseif($parent instanceof Shop) {
            $queryBuilder->where('email_bulk_runs.shop_id', $parent->id);
        } else {
            $queryBuilder->where('email_bulk_runs.organisation_id', $parent->id);
        }

        return $queryBuilder
            ->defaultSort('email_bulk_runs.id')
            ->select([
                'email_bulk_runs.id',
                'email_bulk_runs.subject',
                'email_bulk_runs.state',
            ])
            ->allowedSorts(['email_bulk_runs.subject', 'email_bulk_runs.state'])
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
                ->column(key: 'state', label: __('state'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $emailBulkRuns): AnonymousResourceCollection
    {
        return EmailBulkRunsResource::collection($emailBulkRuns);
    }


    // public function htmlResponse(LengthAwarePaginator $mailshots, ActionRequest $request): Response
    // {

    //     return Inertia::render(
    //         'Mail/Mailshots',
    //         [
    //             'breadcrumbs' => $this->getBreadcrumbs(
    //                 $request->route()->getName(),
    //                 $request->route()->originalParameters(),
    //                 $this->parent
    //             ),
    //             'title'       => __('mailshots'),
    //             'pageHead'    => [
    //                 'title'    => __('mailshots'),
    //                 'actions'  => [
    //                     [
    //                         'type'    => 'button',
    //                         'style'   => 'create',
    //                         'label'   => __('mailshot'),
    //                         'route'   => [
    //                             'name'       => 'grp.org.shops.show.marketing.mailshots.create',
    //                             'parameters' => array_values($request->route()->originalParameters())
    //                         ]
    //                     ]
    //                 ],
    //             ],
    //             'data' => MailshotResource::collection($mailshots),
    //         ]
    //     )->table($this->tableStructure($this->parent));
    // }

    // public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    // {
    //     $this->parent = $organisation;
    //     $this->initialisation($organisation, $request);
    //     return $this->handle($organisation);
    // }

    // public function inShop(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    // {
    //     $this->parent = $organisation;
    //     $this->initialisationFromShop($shop, $request);

    //     return $this->handle($organisation);
    // }

    // /** @noinspection PhpUnused */
    // public function inOutbox(Outbox $outbox, ActionRequest $request): LengthAwarePaginator
    // {
    //     $this->initialisation($request);
    //     return $this->handle($outbox);
    // }

    // /** @noinspection PhpUnused */
    // public function inPostRoomInShop(PostRoom $postRoom, Outbox $outbox, ActionRequest $request): LengthAwarePaginator
    // {
    //     $this->initialisation($request);
    //     return $this->handle($outbox);
    // }


    // /** @noinspection PhpUnused */
    // public function inOutboxInShop(Outbox $outbox, ActionRequest $request): LengthAwarePaginator
    // {
    //     $this->initialisation($request);
    //     return $this->handle($outbox);
    // }
}

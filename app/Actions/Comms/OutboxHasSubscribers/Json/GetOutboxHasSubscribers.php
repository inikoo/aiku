<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 05-03-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Comms\OutboxHasSubscribers\Json;

use App\Actions\OrgAction;
use App\Http\Resources\Mail\OutboxHasSubscribersResource;
use App\Models\Comms\Outbox;
use App\Models\Comms\OutBoxHasSubscribers;
use App\Models\Fulfilment\Fulfilment;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class GetOutboxHasSubscribers extends OrgAction
{
    public function handle(Outbox $parent): LengthAwarePaginator
    {

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {

            $query->where(function ($query) use ($value) {
                $query->whereWith('users.email', $value)
                    ->orWhereWith('outbox_has_subscribers.external_links', $value);
            });
        });



        $queryBuilder = QueryBuilder::for(OutBoxHasSubscribers::class);
        $queryBuilder->where('outbox_id', $parent->id);
        $queryBuilder->leftJoin('users', 'outbox_has_subscribers.user_id', '=', 'users.id');

        $queryBuilder
            ->defaultSort('services.id')
            ->select([
                'outbox_has_subscribers.id',
                'users.id as user_id',
                'users.email as user_email',
                'outbox_has_subscribers.external_email'
            ]);


        return $queryBuilder->allowedSorts(['id','user_id','user_email','external_email'])
            ->allowedFilters([$globalSearch])
            ->withPaginator(null)
            ->withQueryString();
    }

    //todo review this
    // public function authorize(ActionRequest $request): bool
    // {
    //     if ($request->user() instanceof WebUser) {
    //         return true;
    //     }

    //     $this->canEdit   = $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    //     $this->canDelete = $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");

    //     return $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.view");
    // }

    public function inFulfilment(Fulfilment $fulfilment, Outbox $outbox, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($outbox);
    }

    public function jsonResponse(LengthAwarePaginator $subcriberOutbox): AnonymousResourceCollection
    {
        return OutboxHasSubscribersResource::collection($subcriberOutbox);
    }
}

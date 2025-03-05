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
use App\Http\Resources\Mail\OutboxUsersResource;
use App\Models\Catalogue\Shop;
use App\Models\Comms\Outbox;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\User;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class GetOutboxUsers extends OrgAction
{
    public function handle(Outbox $parent): LengthAwarePaginator
    {

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {

            $query->where(function ($query) use ($value) {
                $query->whereWith('users.name', $value);
            });
        });



        $queryBuilder = QueryBuilder::for(User::class);
        $queryBuilder->where('group_id', $parent->group_id);

        $queryBuilder
            ->defaultSort('name')
            ->select([
                'users.id',
                'users.email',
                'users.name',
            ]);


        return $queryBuilder->allowedSorts(['id','email', 'name'])
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
        return OutboxUsersResource::collection($subcriberOutbox);
    }
}

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
use App\Actions\Traits\Authorisations\WithCommsAuthorisation;
use App\Http\Resources\Mail\OutboxUsersResource;
use App\Models\Comms\Outbox;
use App\Models\SysAdmin\User;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class GetOutboxUsers extends OrgAction
{
    use WithCommsAuthorisation;
    public function handle(Outbox $parent): LengthAwarePaginator
    {

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {

            $query->where(function ($query) use ($value) {
                $query->whereWith('users.username', $value)
                    ->orWhereWith('users.contact_name', $value);
            });
        });



        $queryBuilder = QueryBuilder::for(User::class);
        $queryBuilder->where('group_id', $parent->group_id);

        $queryBuilder
            ->defaultSort('username')
            ->select([
                'users.id',
                'users.email',
                'users.username',
                'users.contact_name',
            ]);


        return $queryBuilder->allowedSorts(['id','email', 'username'])
            ->allowedFilters([$globalSearch])
            ->withPaginator(null)
            ->withQueryString();
    }

    public function asController(Outbox $outbox, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromShop($outbox->shop, $request);

        return $this->handle($outbox);
    }

    public function jsonResponse(LengthAwarePaginator $subcriberOutbox): AnonymousResourceCollection
    {
        return OutboxUsersResource::collection($subcriberOutbox);
    }
}

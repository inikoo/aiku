<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 10:15:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Chat\UI;

use App\Actions\Chat\ChatSession\DraftChatReply;
use App\Actions\Chat\WithChatAgentAuthorisation;
use App\Actions\OrgAction;
use App\Enums\CRM\Livechat\ChatAiDraftStatusEnum;
use App\Enums\CRM\Livechat\ChatEventTypeEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\Chat\ChatMessage;
use App\Models\SysAdmin\User;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Everything Aiku left for staff on a chat, newest first: the drafts it wrote (what staff then did
 * with each, its note and its buttons) and the internal notes it left where it could not confirm
 * an answer, each linked to its chat. Only the shops whose chats the user may see are listed.
 */
class IndexChatAikuDrafts extends OrgAction
{
    use WithChatAgentAuthorisation;

    /** @var Collection<int, int> */
    private Collection $shopIds;

    public function authorize(ActionRequest $request): bool
    {
        $this->shopIds = $this->shopIdsFor($request->user());

        return $this->shopIds->isNotEmpty();
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromGroup(app('group'), $request);

        return $this->handle($this->shopIds);
    }

    /**
     * @return Collection<int, int>
     */
    public function shopIdsFor(User $user): Collection
    {
        setPermissionsTeamId($user->group_id);

        return Shop::with('fulfilment')->where('group_id', $user->group_id)->get()
            ->filter(fn (Shop $shop) => $this->userCanViewChatOnShop($user, $shop))
            ->pluck('id')
            ->values();
    }

    /**
     * @param  Collection<int, int>  $shopIds
     */
    public function handle(Collection $shopIds, ?string $prefix = null): LengthAwarePaginator
    {
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($where) use ($value) {
                $where->where('aiku.text', 'ilike', '%'.$value.'%')
                    ->orWhere('aiku.contact', 'ilike', '%'.$value.'%')
                    ->orWhere('aiku.shop_name', 'ilike', '%'.$value.'%');
            });
        });

        return QueryBuilder::for(ChatMessage::withoutGlobalScopes()->fromSub($this->items($shopIds), 'aiku'))
            ->defaultSort('-at')
            ->allowedSorts(['at', 'kind', 'shop_name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    /**
     * @param  Collection<int, int>  $shopIds
     */
    private function items(Collection $shopIds): \Illuminate\Database\Query\Builder
    {
        $common = [
            'chat_sessions.ulid as session_ulid',
            'chat_sessions.channel as channel',
            'shops.name as shop_name',
            'organisations.slug as organisation_slug',
            DB::raw("coalesce(chat_sessions.metadata->>'name', chat_sessions.metadata->>'email_from', chat_sessions.metadata->>'email') as contact"),
        ];
        $shops = fn ($query) => $query
            ->join('chat_sessions', 'chat_sessions.id', '=', $query->from === 'chat_ai_drafts' ? 'chat_ai_drafts.chat_session_id' : 'chat_events.chat_session_id')
            ->join('shops', 'shops.id', '=', 'chat_sessions.shop_id')
            ->join('organisations', 'organisations.id', '=', 'shops.organisation_id')
            ->whereIn('shops.id', $shopIds)
            ->whereNull('chat_sessions.deleted_at');

        $drafts = DB::table('chat_ai_drafts')
            ->tap($shops)
            ->whereRaw("chat_ai_drafts.facts->>'mode' = ?", [DraftChatReply::ASSISTANT])
            ->select([
                DB::raw("'draft' as kind"),
                'chat_ai_drafts.created_at as at',
                'chat_ai_drafts.id as item_id',
                'chat_ai_drafts.text as text',
                'chat_ai_drafts.status as status',
                'chat_ai_drafts.facts as facts',
                DB::raw("(chat_ai_drafts.facts->>'user_id')::integer as asked_by"),
                ...$common,
            ]);

        $notes = DB::table('chat_events')
            ->tap($shops)
            ->where('chat_events.event_type', ChatEventTypeEnum::INTERNAL_NOTE->value)
            ->select([
                DB::raw("'note' as kind"),
                'chat_events.created_at as at',
                'chat_events.id as item_id',
                DB::raw("chat_events.payload->>'text' as text"),
                DB::raw('null::varchar as status'),
                DB::raw('null::jsonb as facts'),
                DB::raw("(chat_events.payload->>'requested_by_user_id')::integer as asked_by"),
                ...$common,
            ]);

        return $drafts->unionAll($notes);
    }

    public function tableStructure(?string $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
                $table->name($prefix)->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->column(key: 'at', label: __('When'), canBeHidden: false, sortable: true)
                ->column(key: 'kind', label: __('What'), canBeHidden: false, sortable: true)
                ->column(key: 'shop_name', label: __('Shop'), sortable: true)
                ->column(key: 'contact', label: __('Customer'))
                ->column(key: 'text', label: __('Draft or note'), canBeHidden: false)
                ->defaultSort('-at');
        };
    }

    public function htmlResponse(LengthAwarePaginator $items, ActionRequest $request): Response
    {
        $askers = User::whereIn('id', $items->getCollection()->pluck('asked_by')->filter()->unique())->pluck('contact_name', 'id');

        $items->getCollection()->transform(function ($row) use ($askers) {
            $facts   = $row->facts ? json_decode($row->facts, true) : [];
            $status  = ChatAiDraftStatusEnum::tryFrom((string) $row->status);
            $actions = collect($facts['actions'] ?? []);

            return [
                'at'           => Carbon::parse($row->at)->toIso8601String(),
                'kind'         => $row->kind,
                'channel'      => $row->channel,
                'shop_name'    => $row->shop_name,
                'contact'      => $row->contact,
                'text'         => $row->text,
                'status'       => $status?->value,
                'status_label' => $status?->label(),
                'staff_note'   => $facts['staff_note'] ?? null,
                'checked'      => count($facts['checked'] ?? []),
                'actions'      => $actions->map(fn (array $action) => [
                    'label' => $action['done']['label'] ?? ($action['label'] ?? ($action['type'] === 'claim' ? str_replace('_', ' ', $action['resolution']).' '.$action['order'] : $action['subject'] ?? '')),
                    'done'  => isset($action['done']),
                ])->values()->all(),
                'premonitions' => $facts['premonitions'] ?? [],
                'asked_by'     => $askers[$row->asked_by] ?? null,
                'url'          => route('grp.org.chat.inbox.conversation', [$row->organisation_slug, trim((string) $row->session_ulid)]),
            ];
        });

        return Inertia::render('Chat/ChatAikuDrafts', [
            'breadcrumbs' => ShowGroupChatAutomation::make()->getBreadcrumbs(),
            'title'       => __('Drafts by Aiku'),
            'pageHead'    => [
                'title'         => __('Drafts by Aiku'),
                'icon'          => ['title' => __('AI assist'), 'icon' => ['fal', 'fa-robot']],
                'subNavigation' => ShowGroupChatAutomation::make()->subNavigation($this->group),
            ],
            'data' => JsonResource::collection($items),
        ])->table($this->tableStructure());
    }
}

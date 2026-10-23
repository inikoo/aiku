<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 15:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Chat\UI;

use App\Actions\Chat\Reports\GetChatAiCoverage;
use App\Actions\OrgAction;
use App\Actions\UI\WithInertia;
use App\Enums\CRM\Livechat\ChatAiOutcomeEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Chat\ChatMessage;
use App\Models\SysAdmin\Group;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * The chats behind one number of the AI coverage report on /chat/ai, with a link to each.
 */
class IndexChatAiCoverageChats extends OrgAction
{
    use WithInertia;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasGroupAccess();
    }

    public function rules(): array
    {
        return [
            'window'  => ['sometimes', 'nullable', 'in:'.implode(',', GetChatAiCoverage::WINDOWS)],
            'from'    => ['sometimes', 'nullable', 'date'],
            'to'      => ['sometimes', 'nullable', 'date'],
            'row'     => ['sometimes', 'nullable', 'string', 'max:40'],
            'channel' => ['sometimes', 'nullable', 'in:'.implode(',', GetChatAiCoverage::CHANNELS)],
            'shop'    => ['sometimes', 'nullable', 'string', 'max:40'],
        ];
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromGroup(app('group'), $request);

        return $this->handle($this->group, $this->validatedData);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function handle(Group $group, array $filters): LengthAwarePaginator
    {
        $coverage = GetChatAiCoverage::make();
        $segments = $coverage->segments($group, $filters['window'] ?? 'overnight', $filters['from'] ?? null, $filters['to'] ?? null);

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($where) use ($value) {
                $where->where('coverage.contact', 'ilike', '%'.$value.'%')
                    ->orWhere('coverage.reason', 'ilike', '%'.$value.'%');
            });
        });

        return QueryBuilder::for(ChatMessage::withoutGlobalScopes()->fromSub($coverage->chats($group, $segments), 'coverage'))
            ->when($filters['channel'] ?? null, fn ($query, $channel) => $query->where('coverage.channel', $channel))
            ->when($filters['shop'] ?? null, fn ($query, $shop) => $query->where('coverage.shop_slug', $shop))
            ->when($this->buckets($filters['row'] ?? null), fn ($query, $buckets) => $query->whereIn('coverage.bucket', $buckets))
            ->defaultSort('-at')
            ->allowedSorts(['at', 'bucket', 'shop_slug'])
            ->allowedFilters([$globalSearch])
            ->withPaginator(null, tableName: request()->route()->getName())
            ->withQueryString();
    }

    /**
     * @return array<int, string>|null
     */
    private function buckets(?string $row): ?array
    {
        return match ($row) {
            null, 'received' => null,
            'worked'         => GetChatAiCoverage::WORKED,
            default          => [$row],
        };
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->column(key: 'at', label: __('Customer wrote'), canBeHidden: false, sortable: true)
                ->column(key: 'contact', label: __('Customer'), canBeHidden: false)
                ->column(key: 'shop_slug', label: __('Shop'), sortable: true)
                ->column(key: 'bucket', label: __('What the AI did'), canBeHidden: false, sortable: true)
                ->column(key: 'reason', label: __('Why'))
                ->defaultSort('-at');
        };
    }

    public function htmlResponse(LengthAwarePaginator $chats, ActionRequest $request): Response
    {
        $chats->getCollection()->transform(fn ($row) => [
            'at'           => $row->at,
            'channel'      => $row->channel,
            'contact'      => $row->contact,
            'shop_slug'    => $row->shop_slug,
            'bucket'       => $row->bucket,
            'bucket_label' => ChatAiOutcomeEnum::tryFrom((string) $row->bucket)?->label() ?? __('Not worked yet'),
            'recorded'     => $row->source === 'recorded',
            'reason'       => $row->reason,
            'url'          => $row->channel === 'whatsapp'
                ? route('grp.org.chat.inbox', [$row->organisation_slug, 'channel' => 'whatsapp', 'session' => trim($row->session_ulid)])
                : route('grp.org.chat.inbox.conversation', [$row->organisation_slug, trim($row->session_ulid)]),
        ]);

        return Inertia::render('Chat/ChatAiCoverageChats', [
            'breadcrumbs' => ShowGroupChatAutomation::make()->getBreadcrumbs(),
            'title'       => __('AI coverage'),
            'pageHead'    => [
                'title'         => __('AI coverage: chats'),
                'icon'          => ['title' => __('AI assist'), 'icon' => ['fal', 'fa-robot']],
                'subNavigation' => ShowGroupChatAutomation::make()->subNavigation($this->group),
            ],
            'back' => route('grp.chat.ai.dashboard', array_filter($request->only(['window', 'from', 'to']))),
            'data' => JsonResource::collection($chats),
        ])->table($this->tableStructure());
    }
}

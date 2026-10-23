<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Mcp\Tools;

use App\Actions\Chat\ChatSession\CloseChatSession;
use App\Actions\Chat\ChatSession\PlanChatClaim;
use App\Actions\Chat\ChatSession\RunChatAiDraftAction;
use App\Enums\Dispatching\DeliveryNoteItem\DeliveryNoteItemReplacementReasonEnum;
use App\Enums\Helpers\Ticket\TicketTypeEnum;
use App\Models\Helpers\Ticket;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Actions\Chat\ChatSession\ConfirmSuggestedChatCustomer;
use App\Actions\Chat\ChatSession\LinkChatSessionCustomer;
use App\Actions\Chat\ChatSession\SendChatMessage;
use App\Actions\Chat\ChatSession\StoreChatEvent;
use App\Actions\Chat\ChatSession\StoreAssistantChatDraft;
use App\Actions\Chat\ChatSession\SuggestChatSessionCustomer;
use App\Actions\Chat\ChatSession\WaitForCustomerReply;
use App\Actions\Helpers\Translations\DetectLanguageWithJev;
use App\Enums\CRM\Livechat\ChatChannelEnum;
use App\Enums\CRM\Livechat\ChatSessionStatusEnum;
use App\Enums\SysAdmin\McpChange\McpChangeTypeEnum;
use App\Listeners\RememberMcpClientName;
use App\Models\Chat\ChatSession;
use App\Models\CRM\Customer;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Acts on a customer chat, by its Chat ID, as the user: save_draft (puts message on the chat as a draft reply the user reviews and sends from Aiku, with an optional staff_note, the facts checked, PRE tickets and one-click actions (claim or staff task) shown on the draft; nothing reaches the customer, no confirm needed; the usual way to help with a reply), reply (sends message to the customer in the user\'s name; on an email chat it is emailed), assign_to_me, close, wait_for_reply (hours 1-720, the chat closes itself if the customer stays silent), stop_waiting, link_customer (customer = their reference, slug or email), unlink_customer or add_note (message = an internal note for staff, shown in the thread in pink and never sent to the customer: what was found out and what to tell them, in the user\'s language). Call customer-service-chat-tool first. Without confirm=true nothing happens and a preview comes back: show the user the exact reply text, in the customer\'s language, and send only after they say yes in their own words. A chat held by a colleague is refused. Only for users enrolled to serve customers through their assistant, except add_note: a note is signed by Aiku and never reaches the customer.')]
class CustomerServiceChatWriteTool extends AikuCustomerServiceTool
{
    use WithMcpChangeLog;

    private const array ACTIONS = ['save_draft', 'reply', 'assign_to_me', 'close', 'wait_for_reply', 'stop_waiting', 'link_customer', 'unlink_customer', 'add_note'];

    public function handle(Request $request): Response
    {
        $request->validate([
            'chat_id'      => ['required', 'string', 'max:40'],
            'action'       => ['required', 'in:'.implode(',', self::ACTIONS)],
            'message'      => ['required_if:action,reply,save_draft,add_note', 'nullable', 'string', 'max:5000'],
            'staff_note'   => ['sometimes', 'nullable', 'string', 'max:2000'],
            'checked'      => ['sometimes', 'nullable', 'array', 'max:12'],
            'checked.*'    => ['string', 'max:300'],
            'premonitions'   => ['sometimes', 'nullable', 'array', 'max:3'],
            'premonitions.*' => ['string', 'max:20'],
            'actions'                    => ['sometimes', 'nullable', 'array', 'max:4'],
            'actions.*.type'             => ['required', 'in:claim,task'],
            'actions.*.order'            => ['required_if:actions.*.type,claim', 'nullable', 'string', 'max:64'],
            'actions.*.resolution'       => ['required_if:actions.*.type,claim', 'nullable', 'in:'.implode(',', PlanChatClaim::RESOLUTIONS)],
            'actions.*.items'            => ['required_if:actions.*.type,claim', 'nullable', 'array', 'max:80'],
            'actions.*.items.*.code'     => ['required', 'string'],
            'actions.*.items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'actions.*.reason'           => ['sometimes', 'nullable', Rule::enum(DeliveryNoteItemReplacementReasonEnum::class)],
            'actions.*.percentage'       => ['sometimes', 'nullable', 'numeric', 'gt:0', 'max:100'],
            'actions.*.label'            => ['required_if:actions.*.type,task', 'nullable', 'string', 'max:80'],
            'actions.*.subject'          => ['required_if:actions.*.type,task', 'nullable', 'string', 'max:255'],
            'actions.*.description'      => ['sometimes', 'nullable', 'string', 'max:4000'],
            'actions.*.department'       => ['sometimes', 'nullable', 'string', 'max:64'],
            'actions.*.assignee'         => ['sometimes', 'nullable', 'string', 'max:64'],
            'hours'        => ['required_if:action,wait_for_reply', 'nullable', 'integer', 'min:1', 'max:720'],
            'customer'     => ['required_if:action,link_customer', 'nullable', 'string', 'max:255'],
            'confirm'      => ['sometimes', 'boolean'],
            'request_text' => ['required_if:confirm,true', 'nullable', 'string', 'max:4000'],
        ]);

        $isNote = $request->string('action')->toString() === 'add_note';

        $denied = $isNote ? null : $this->notEnrolled($request);
        if ($denied) {
            return $denied;
        }

        [$chatSession, $error] = $this->chatSessionFor($request, toActOnIt: true);
        if ($error) {
            return $error;
        }

        $action   = $request->string('action')->toString();
        $customer = null;

        if ($action === 'link_customer') {
            $customer = $this->customerToLink($chatSession, $request->string('customer')->toString());

            if (!$customer) {
                return Response::error("No customer with a login in {$chatSession->shop->name} matches '{$request->string('customer')}'. Use their reference, slug or email. Nothing was changed.");
            }
        }

        if (in_array($action, ['save_draft', 'close', 'wait_for_reply', 'stop_waiting']) && $chatSession->status === ChatSessionStatusEnum::CLOSED) {
            return Response::error('This chat is closed. Nothing was changed.');
        }

        if ($action === 'save_draft') {
            return $this->saveDraft($request, $chatSession);
        }

        if ($action === 'add_note') {
            return $this->addNote($request, $chatSession);
        }

        if (!$request->boolean('confirm')) {
            return $this->askToConfirm(array_filter([
                'action'   => $action,
                'chat'     => $chatSession->ulid,
                'to'       => $action === 'reply' ? (data_get($chatSession->metadata, 'email_from') ?? $this->customerOf($chatSession)?->name ?? 'the visitor') : null,
                'sent_by'  => $action === 'reply' ? ($chatSession->channel === ChatChannelEnum::EMAIL ? 'email from the shop mailbox' : 'chat') : null,
                'message'  => $action === 'reply' ? $request->get('message') : null,
                'hours'    => $action === 'wait_for_reply' ? $request->integer('hours') : null,
                'customer' => $customer ? $customer->name.' ('.$customer->reference.', '.$customer->email.')' : null,
            ]));
        }

        try {
            $this->recordChange(
                $request,
                McpChangeTypeEnum::CUSTOMER_SERVICE,
                str_replace('_', ' ', $action).' on chat '.$chatSession->ulid,
                ['chat_session_id' => $chatSession->id],
                fn () => $this->act($chatSession, $action, $request, $customer),
                ['action' => $action, 'message' => $request->get('message')]
            );
        } catch (ValidationException $exception) {
            return Response::error(implode(' ', $exception->validator->errors()->all()).' Nothing was changed.');
        }

        $chatSession->refresh();

        return Response::json([
            'done'          => $action,
            'chat'          => $chatSession->ulid,
            'status'        => $chatSession->status?->value,
            'waiting_until' => WaitForCustomerReply::until($chatSession),
            'customer'      => $this->customerOf($chatSession)?->name,
            'change_log_id' => $this->mcpChange?->id,
        ]);
    }

    /**
     * A draft only lands in the agent's draft box, so it needs no confirmation: discarding it there
     * is the undo. It is refused on a colleague's chat and in a language the customer does not write.
     */
    private function saveDraft(Request $request, ChatSession $chatSession): Response
    {
        if (!$this->userCanDisposeOfChat($request->user(), $chatSession)) {
            return Response::error($this->chatHeldByAnotherAgentMessage($chatSession).' Nothing was saved.');
        }

        $text     = trim($request->string('message')->toString());
        $language = $this->replyLanguage($chatSession);
        $written  = $language ? DetectLanguageWithJev::run($text, [$language]) : null;

        if ($written && $written->id !== $language->id) {
            return Response::error("Write the draft in {$language->name}, the language of this conversation. Nothing was saved.");
        }

        $actions = [];
        foreach (array_values($request->get('actions') ?? []) as $position => $action) {
            $refused = $this->refusedDraftAction($chatSession, $action);
            if ($refused) {
                return Response::error('Action '.($position + 1).": $refused Nothing was saved.");
            }
            $actions[] = array_filter(Arr::only($action, ['type', 'order', 'resolution', 'items', 'reason', 'percentage', 'label', 'subject', 'description', 'department', 'assignee']), fn ($value) => $value !== null && $value !== '');
        }

        $premonitions = Ticket::where('group_id', $chatSession->shop->group_id)
            ->where('type', TicketTypeEnum::PREMONITION)
            ->whereIn('reference', array_map('strtoupper', $request->get('premonitions') ?? []))
            ->pluck('reference')
            ->all();

        $draft = StoreAssistantChatDraft::run(
            $chatSession,
            $request->user(),
            $text,
            $request->sessionId() ? Cache::get(RememberMcpClientName::key($request->sessionId())) : null
        );

        $draft->update(['facts' => $draft->facts + array_filter([
            'staff_note'   => trim((string) $request->get('staff_note')) ?: null,
            'checked'      => array_values(array_filter(array_map('trim', $request->get('checked') ?? []))),
            'premonitions' => $premonitions,
            'actions'      => $actions,
        ])]);

        return Response::json([
            'saved'     => 'draft',
            'chat'      => $chatSession->ulid,
            'draft_id'  => $draft->id,
            'next_step' => 'Nothing was sent. The draft is waiting on the chat in Aiku, where the user can send it, edit it or discard it. Any [[gap]] left in it must be filled there before it can be sent.',
        ]);
    }

    /**
     * A claim button is planned when the draft is saved, so a draft never offers a refund or a
     * replacement that the order no longer allows; a task needs someone to do it.
     *
     * @param  array<string, mixed>  $action
     */
    private function refusedDraftAction(ChatSession $chatSession, array $action): ?string
    {
        if ($action['type'] === 'task') {
            return empty($action['department']) && empty($action['assignee']) ? 'a task needs a department or an assignee.' : null;
        }

        $order = RunChatAiDraftAction::order($chatSession, (string) $action['order']);

        if (!$order) {
            return "{$action['order']} is not an order of this customer.";
        }

        return PlanChatClaim::run($chatSession, $order, $action['resolution'], $action['items'], $action['reason'] ?? null, isset($action['percentage']) ? (float) $action['percentage'] : null)['error'];
    }

    /**
     * A note stays in the thread for every colleague to read and has no undo, so it is previewed first.
     * It is allowed on a colleague's chat and on a closed one: that is where what was found out is needed.
     */
    private function addNote(Request $request, ChatSession $chatSession): Response
    {
        $text = trim($request->string('message')->toString());

        if (!$request->boolean('confirm')) {
            return $this->askToConfirm(['action' => 'add_note', 'chat' => $chatSession->ulid, 'note' => $text]);
        }

        $note = StoreChatEvent::make()->internalNote($chatSession, $text, $request->user()->id);

        return Response::json([
            'done'    => 'add_note',
            'chat'    => $chatSession->ulid,
            'note_id' => $note->id,
        ]);
    }

    /**
     * @throws \Throwable
     */
    private function act(ChatSession $chatSession, string $action, Request $request, ?Customer $customer): void
    {
        match ($action) {
            'reply'           => SendChatMessage::make()->replyAsAgent($chatSession, $request->string('message')->toString()),
            'assign_to_me'    => $this->claim($chatSession, $request),
            'close'           => CloseChatSession::make()->handle($chatSession, $this->chatAgent->id),
            'wait_for_reply'  => WaitForCustomerReply::make()->handle($chatSession, $request->integer('hours')),
            'stop_waiting'    => WaitForCustomerReply::stop($chatSession),
            'link_customer'   => $this->link($chatSession, $customer),
            'unlink_customer' => $this->unlink($chatSession),
        };
    }

    /**
     * Picked up the way a reply picks a chat up, not through the routing pool: whoever asks their
     * assistant is rarely sitting in the inbox, and being offline there must not stop them.
     *
     * @throws ValidationException
     */
    private function claim(ChatSession $chatSession, Request $request): void
    {
        $refusal = SendChatMessage::make()->claimUnheldChat($chatSession, $request->user(), $this->chatAgent);

        if ($refusal) {
            throw ValidationException::withMessages(['chat_id' => $refusal['message']]);
        }
    }

    private function customerToLink(ChatSession $chatSession, string $identifier): ?Customer
    {
        $identifier = strtolower(trim($identifier));

        return Customer::where('shop_id', $chatSession->shop_id)
            ->whereHas('webUsers')
            ->where(fn ($query) => $query->whereRaw('lower(reference) = ?', [$identifier])
                ->orWhereRaw('lower(slug) = ?', [$identifier])
                ->orWhereRaw('lower(email) = ?', [$identifier]))
            ->first();
    }

    private function link(ChatSession $chatSession, Customer $customer): void
    {
        $chatSession->update([
            'suggested_customer_id'  => $customer->id,
            'suggestion_basis'       => SuggestChatSessionCustomer::BASIS_MANUAL,
            'suggestion_rejected_at' => null,
        ]);

        ConfirmSuggestedChatCustomer::make()->handle($chatSession, $this->chatAgent, true);
    }

    /**
     * @throws ValidationException
     */
    private function unlink(ChatSession $chatSession): void
    {
        $response = LinkChatSessionCustomer::make()->unlink($chatSession);

        if (!data_get($response->getData(true), 'success')) {
            throw ValidationException::withMessages(['customer' => data_get($response->getData(true), 'message', 'The customer could not be unlinked.')]);
        }
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'chat_id'      => $schema->string()->description('Chat ID copied from the Overview panel of the chat')->required(),
            'action'       => $schema->string()->enum(self::ACTIONS)->description('What to do on the chat')->required(),
            'message'      => $schema->string()->description('For add_note: the note for staff. For save_draft and reply: the full text for the customer, plain text, in the language reply_in names, signed as the user would sign it. In a draft, [[what to check]] marks something only the user can decide'),
            'staff_note'   => $schema->string()->description('For save_draft only: a short note for staff shown on the draft itself, never sent to the customer, in the user\'s language. Start it with "Before sending:" when staff must do something first (refund, credit, replacement); otherwise what was checked and anything uncertain'),
            'checked'      => $schema->array()->items($schema->string())->description('For save_draft only: up to 12 short lines of what was checked in Aiku for this answer, each a fact with its reference (e.g. "AWD196419 dispatched 13 Oct, Royal Mail XQ573801258GB"), shown to staff on the draft'),
            'premonitions' => $schema->array()->items($schema->string())->description('For save_draft only: PRE ticket references raised or found for a problem in this chat, shown to staff on the draft with their live status'),
            'actions'      => $schema->array()->items($schema->object([
                'type'        => $schema->string()->enum(['claim', 'task'])->required(),
                'order'       => $schema->string()->description('claim: the dispatched order reference'),
                'resolution'  => $schema->string()->enum(PlanChatClaim::RESOLUTIONS)->description('claim: refund_to_balance, replacement or next_order'),
                'items'       => $schema->array()->items($schema->object([
                    'code'     => $schema->string()->required(),
                    'quantity' => $schema->number()->required(),
                ]))->description('claim: product codes and quantities, from the claimable list'),
                'reason'      => $schema->string()->enum(array_column(DeliveryNoteItemReplacementReasonEnum::cases(), 'value'))->description('claim: why'),
                'percentage'  => $schema->number()->description('claim refund_to_balance: part to refund, 100 by default'),
                'label'       => $schema->string()->description('task: the button text, short, in the user\'s language, e.g. "Ask the warehouse to dispatch AWD197367 today"'),
                'subject'     => $schema->string()->description('task: the task subject for the department'),
                'description' => $schema->string()->description('task: what to do and why, with references'),
                'department'  => $schema->string()->description('task: department slug, e.g. warehouse'),
                'assignee'    => $schema->string()->description('task: aiku username, instead of a department'),
            ]))->description('For save_draft only: up to 4 one-click buttons shown on the draft for what staff must do before sending: settle a claim (the amount is shown and checked when clicked) or ask a department as a staff task. Nothing runs until a staff member clicks. Claims are checked when the draft is saved and a draft with one that cannot be done is refused'),
            'hours'        => $schema->integer()->description('For wait_for_reply: hours to wait for the customer, 24, 72 or 168 are the usual ones')->min(1)->max(720),
            'customer'     => $schema->string()->description('For link_customer: the customer\'s reference, slug or email'),
            'confirm'      => $schema->boolean()->description('true only after the user has seen the preview and said yes'),
            'request_text' => $schema->string()->description('The user\'s request, verbatim, kept in the AI changes log; required with confirm=true'),
        ];
    }
}

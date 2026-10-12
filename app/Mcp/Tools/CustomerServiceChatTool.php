<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Mcp\Tools;

use App\Actions\Chat\ChatSession\GetChatClaimCase;
use App\Actions\Chat\ChatSession\WaitForCustomerReply;
use App\Actions\Ordering\Order\SaveOrderModification;
use App\Enums\CRM\Livechat\ChatAssignmentStatusEnum;
use App\Models\Chat\ChatMessage;
use App\Models\Chat\ChatSession;
use App\Models\CRM\Customer;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Opens a customer chat by the Chat ID staff copy from the Overview panel of the chat (26 characters, e.g. 01M3YHCN5YPXEDJN1MYJG1DGEF): the conversation, who holds it, the customer with their balance and their latest orders. Pass order with one of their order references to also get its lines, delivery address, notes, whether it can still be modified, and what can be claimed on it. Always call this first, before any other customer service tool, and read the conversation before proposing anything.')]
#[IsReadOnly]
class CustomerServiceChatTool extends AikuCustomerServiceTool
{
    public function handle(Request $request): Response
    {
        $request->validate([
            'chat_id' => ['required', 'string', 'max:40'],
            'order'   => ['sometimes', 'nullable', 'string', 'max:64'],
        ]);

        [$chatSession, $error] = $this->chatSessionFor($request, toActOnIt: false);
        if ($error) {
            return $error;
        }

        $customer = $this->customerOf($chatSession);
        $order    = null;

        if ($request->filled('order')) {
            [$order, $error] = $this->orderOf($chatSession, (string) $request->string('order'));
            if ($error) {
                return $error;
            }
        }

        return Response::json(array_filter([
            'chat'     => $this->chat($chatSession, $request),
            'messages' => $this->messages($chatSession),
            'customer' => $customer ? $this->customer($customer) : 'Not linked to a customer yet',
            'orders'   => $customer ? $this->orders($customer) : null,
            'order'    => $order ? $this->order($chatSession, $customer, $order) : null,
        ], fn ($value) => $value !== null));
    }

    private function chat(ChatSession $chatSession, Request $request): array
    {
        return [
            'chat_id'       => $chatSession->ulid,
            'shop'          => $chatSession->shop->name,
            'channel'       => $chatSession->channel?->value,
            'status'        => $chatSession->status?->value,
            'subject'       => data_get($chatSession->metadata, 'email_subject'),
            'from'          => data_get($chatSession->metadata, 'email_from') ?? data_get($chatSession->metadata, 'email'),
            'topic'         => $chatSession->topic,
            'summary'       => data_get($chatSession->metadata, 'ai_summary.summary'),
            'held_by'       => $chatSession->assignments()->where('status', ChatAssignmentStatusEnum::ACTIVE->value)->latest('id')->first()?->chatAgent?->user?->contact_name,
            'waiting_until' => WaitForCustomerReply::until($chatSession),
            'can_write'     => (bool) $request->user()?->can_use_mcp_customer_service,
        ];
    }

    private function messages(ChatSession $chatSession): array
    {
        return $chatSession->messages()
            ->latest('id')
            ->limit(30)
            ->get()
            ->reverse()
            ->map(fn (ChatMessage $message) => [
                'at'         => $message->created_at?->toDateTimeString(),
                'from'       => $message->sender_type?->value,
                'text'       => Str::limit((string) $message->message_text, 3000),
                'attachment' => $message->media_id ? true : null,
            ])
            ->map(fn (array $message) => array_filter($message, fn ($value) => $value !== null))
            ->values()
            ->all();
    }

    private function customer(Customer $customer): array
    {
        return [
            'name'      => $customer->name,
            'reference' => $customer->reference,
            'slug'      => $customer->slug,
            'email'     => $customer->email,
            'company'   => $customer->company_name,
            'balance'   => (float) $customer->balance,
            'currency'  => $customer->shop->currency?->code,
        ];
    }

    private function orders(Customer $customer): array
    {
        return $customer->orders()
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (Order $order) => [
                'reference' => $order->reference,
                'state'     => $order->state->value,
                'date'      => $order->date?->toDateString(),
                'total'     => (float) $order->total_amount,
            ])
            ->all();
    }

    private function order(ChatSession $chatSession, Customer $customer, Order $order): array
    {
        $claim = GetChatClaimCase::run($chatSession, $customer, $order->reference);

        return [
            'reference'           => $order->reference,
            'state'               => $order->state->value,
            'total'               => (float) $order->total_amount,
            'currency'            => $order->currency?->code,
            'products_can_change' => SaveOrderModification::acceptsNewProducts($order),
            'delivery_address'    => $order->deliveryAddress
                ? $order->deliveryAddress->only(['address_line_1', 'address_line_2', 'locality', 'administrative_area', 'postal_code', 'country_code'])
                : null,
            'notes'               => array_filter($order->only(['customer_notes', 'public_notes', 'internal_notes', 'shipping_notes', 'private_warehouse_note'])),
            'lines'               => $order->transactions()->where('model_type', 'Product')->with('asset:id,code,name')->orderBy('id')->get()
                ->map(fn (Transaction $transaction) => [
                    'code'     => $transaction->asset?->code,
                    'name'     => $transaction->asset?->name,
                    'quantity' => (float) $transaction->quantity_ordered,
                    'net'      => (float) $transaction->net_amount,
                ])
                ->all(),
            'deliveries'          => $order->deliveryNotes()->orderBy('delivery_notes.id')->get()
                ->map(fn (DeliveryNote $deliveryNote) => [
                    'reference' => $deliveryNote->reference,
                    'type'      => $deliveryNote->type->value,
                    'state'     => $deliveryNote->state->value,
                ])
                ->all(),
            'claim'               => $claim && $claim['order']['reference'] === $order->reference ? [
                'too_old'       => $claim['order']['too_old'],
                'max_age_days'  => $claim['max_age_days'],
                'suggested_reason' => $claim['reason'],
                'reasons'       => collect($claim['reasons'])->pluck('value')->all(),
                'claimable'     => collect($claim['lines'])->filter(fn (array $line) => $line['claimable'] > 0)
                    ->map(fn (array $line) => [
                        'code'      => $line['code'],
                        'name'      => $line['name'],
                        'claimable' => $line['claimable'],
                        'mentioned_by_customer' => $line['mentioned'],
                    ])
                    ->values()
                    ->all(),
                'already_given' => array_filter([
                    'refunds'       => $claim['refunds'],
                    'replacements'  => $claim['replacements'],
                    'in_next_order' => $claim['follow_ons'],
                ]),
            ] : null,
        ];
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'chat_id' => $schema->string()->description('Chat ID copied from the Overview panel of the chat')->required(),
            'order'   => $schema->string()->description('Reference of one of the customer\'s orders, to see its lines, address, notes and what can be claimed'),
        ];
    }
}

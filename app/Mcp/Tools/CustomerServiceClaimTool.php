<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Mcp\Tools;

use App\Actions\Accounting\Invoice\RefundClaimToBalance;
use App\Actions\Chat\ChatSession\GetChatClaimCase;
use App\Actions\Dispatching\DeliveryNote\StoreReplacementDeliveryNote;
use App\Actions\Ordering\UpcomingTransaction\StoreClaimFollowOns;
use App\Enums\Dispatching\DeliveryNoteItem\DeliveryNoteItemReplacementReasonEnum;
use App\Enums\SysAdmin\Authorisation\ShopPermissionsEnum;
use App\Enums\SysAdmin\McpChange\McpChangeTypeEnum;
use App\Models\Ordering\Order;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Description('Settles a claim (damaged, missing or wrong items) on a dispatched order of the customer of a chat, the same three ways as the Claim panel: refund_to_balance credits the customer\'s balance for the claimed items (percentage below 100 for a goodwill part refund), replacement creates a replacement delivery the warehouse sends now, next_order adds the items free to the customer\'s next order. items are product codes with the quantity claimed, taken from the claimable list of customer-service-chat-tool called with the order. Without confirm=true nothing happens and a preview with the amount comes back: show it to the user and settle only after they say yes in their own words. A credit or a replacement cannot be undone from the AI changes log. Only for users enrolled to serve customers through their assistant.')]
class CustomerServiceClaimTool extends AikuCustomerServiceTool
{
    use WithMcpChangeLog;

    private const array RESOLUTIONS = ['refund_to_balance', 'replacement', 'next_order'];

    public function handle(Request $request): Response
    {
        $request->validate([
            'chat_id'          => ['required', 'string', 'max:40'],
            'order'            => ['required', 'string', 'max:64'],
            'resolution'       => ['required', 'in:'.implode(',', self::RESOLUTIONS)],
            'items'            => ['required', 'array', 'min:1', 'max:80'],
            'items.*.code'     => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'reason'           => ['sometimes', 'nullable', Rule::enum(DeliveryNoteItemReplacementReasonEnum::class)],
            'percentage'       => ['sometimes', 'nullable', 'numeric', 'gt:0', 'max:100'],
            'note'             => ['sometimes', 'nullable', 'string', 'max:4000'],
            'confirm'          => ['sometimes', 'boolean'],
            'request_text'     => ['required_if:confirm,true', 'nullable', 'string', 'max:4000'],
        ]);

        $denied = $this->notEnrolled($request);
        if ($denied) {
            return $denied;
        }

        [$chatSession, $error] = $this->chatSessionFor($request, toActOnIt: true);
        if ($error) {
            return $error;
        }

        [$order, $error] = $this->orderOf($chatSession, (string) $request->string('order'));
        if ($error) {
            return $error;
        }

        $resolution = $request->string('resolution')->toString();

        $denied = $resolution === 'replacement'
            ? $this->cannotOnShop($request, $chatSession, ShopPermissionsEnum::ORDERS_EDIT, 'create replacements')
            : $this->cannotOnShop($request, $chatSession, ShopPermissionsEnum::CRM_EDIT, 'settle claims');
        if ($denied) {
            return $denied;
        }

        $claim = GetChatClaimCase::run($chatSession, $this->customerOf($chatSession), $order->reference);

        if (!$claim || $claim['order']['reference'] !== $order->reference || !$claim['lines']) {
            return Response::error("{$order->reference} has nothing delivered to claim on. Nothing was changed.");
        }

        if ($claim['order']['too_old']) {
            return Response::error("Claims are taken for {$claim['max_age_days']} days after the order is dispatched, and {$order->reference} is older. Nothing was changed.");
        }

        $reason     = $request->get('reason') ?: $claim['reason'];
        $percentage = $resolution === 'refund_to_balance' ? (float) ($request->get('percentage') ?: 100) : 100.0;
        $lines      = collect($claim['lines'])->keyBy(fn (array $line) => strtolower((string) $line['code']));
        $claimed    = [];
        $amount     = 0.0;

        $items      = collect($request->get('items'))
            ->groupBy(fn (array $item) => strtolower(trim($item['code'])))
            ->map(fn ($sameCode) => ['code' => trim($sameCode->first()['code']), 'quantity' => (float) $sameCode->sum('quantity')])
            ->values();

        foreach ($items as $item) {
            $line = $lines->get(strtolower($item['code']));

            if (!$line) {
                return Response::error("{$item['code']} was not delivered on {$order->reference}. Delivered: ".$lines->pluck('code')->implode(', ').'. Nothing was changed.');
            }

            if ((float) $item['quantity'] > $line['claimable'] + 0.0005) {
                return Response::error("Only {$line['claimable']} of {$line['code']} is left to claim on {$order->reference}: the rest is already replaced, refunded or waiting in their next order. Nothing was changed.");
            }

            $claimed[] = [
                'id'       => $line['id'],
                'code'     => $line['code'],
                'name'     => $line['name'],
                'quantity' => (float) $item['quantity'],
                'reason'   => $reason,
            ];

            $amount += ($claim['invoiced'][$line['transaction_id']] ?? 0) * min(1, (float) $item['quantity'] / max($line['ordered'], 0.001)) * $claim['tax_ratio'] * $percentage / 100;
        }

        $customer = $order->customer;

        if (!$request->boolean('confirm')) {
            return $this->askToConfirm(array_filter([
                'resolution' => $resolution,
                'order'      => $order->reference,
                'customer'   => $customer->name,
                'reason'     => DeliveryNoteItemReplacementReasonEnum::from($reason)->label(),
                'items'      => collect($claimed)->map(fn (array $item) => $item['code'].' x'.$item['quantity'].' '.$item['name'])->all(),
                'credit'     => $resolution === 'refund_to_balance' ? 'about '.round($amount, 2).' '.$claim['currency'].' to the balance of '.$customer->name.' (now '.(float) $customer->balance.')' : null,
                'percentage' => $resolution === 'refund_to_balance' && $percentage < 100 ? $percentage : null,
                'note'       => $request->get('note'),
            ]));
        }

        try {
            $result = $this->recordChange(
                $request,
                McpChangeTypeEnum::CUSTOMER_SERVICE,
                str_replace('_', ' ', $resolution).' for '.collect($claimed)->map(fn (array $item) => $item['code'].' x'.$item['quantity'])->implode(', ').' of '.$order->reference,
                ['chat_session_id' => $chatSession->id, 'order_id' => $order->id],
                fn () => $this->settle($order, $resolution, $claimed, $reason, $percentage, $request->get('note')),
                ['action' => $resolution, 'items' => $claimed, 'percentage' => $percentage]
            );
        } catch (ValidationException $exception) {
            return Response::error(implode(' ', $exception->validator->errors()->all()).' Nothing was changed.');
        } catch (HttpException $exception) {
            return Response::error($exception->getMessage().' Nothing was changed.');
        }

        return Response::json([
            'done'          => $resolution,
            'order'         => $order->reference,
            'result'        => $result,
            'balance'       => (float) $customer->refresh()->balance,
            'currency'      => $claim['currency'],
            'change_log_id' => $this->mcpChange?->id,
        ]);
    }

    /**
     * @param  array<int, array{id: int, code: string, name: string, quantity: float, reason: string}>  $claimed
     *
     * @throws \Throwable
     */
    private function settle(Order $order, string $resolution, array $claimed, string $reason, float $percentage, ?string $note): array
    {
        $items = array_map(fn (array $item) => ['id' => $item['id'], 'quantity' => $item['quantity'], 'reason' => $item['reason']], $claimed);

        if ($resolution === 'refund_to_balance') {
            $refund = RefundClaimToBalance::make()->handle($order, $items, $percentage);

            return ['refund' => $refund->reference, 'credited' => abs((float) $refund->total_amount)];
        }

        if ($resolution === 'next_order') {
            return [
                'in_next_order' => StoreClaimFollowOns::make()->handle($order, $items, $reason, $note)
                    ->map(fn ($followOn) => $followOn->product?->code.' x'.(float) $followOn->quantity)
                    ->all(),
            ];
        }

        $deliveryNote = StoreReplacementDeliveryNote::make()->action($order, array_filter([
            'delivery_note_items'    => $items,
            'private_warehouse_note' => $note,
        ]));

        return ['replacement' => $deliveryNote->reference, 'state' => $deliveryNote->state->value];
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'chat_id'      => $schema->string()->description('Chat ID copied from the Overview panel of the chat')->required(),
            'order'        => $schema->string()->description('Reference of the dispatched order the claim is about, e.g. GB589603')->required(),
            'resolution'   => $schema->string()->enum(self::RESOLUTIONS)->description('How the claim is settled')->required(),
            'items'        => $schema->array()->items($schema->object([
                'code'     => $schema->string()->description('Product code as delivered, e.g. SelCH-03')->required(),
                'quantity' => $schema->number()->description('Quantity claimed, in the units the customer ordered')->required(),
            ]))->description('What is claimed')->required(),
            'reason'       => $schema->string()->enum(array_column(DeliveryNoteItemReplacementReasonEnum::cases(), 'value'))->description('Why; defaults to what the conversation suggests'),
            'percentage'   => $schema->number()->description('refund_to_balance only: part of the value to refund, 100 by default'),
            'note'         => $schema->string()->description('replacement: private note for the warehouse; next_order: private note on the items'),
            'confirm'      => $schema->boolean()->description('true only after the user has seen the preview and said yes'),
            'request_text' => $schema->string()->description('The user\'s request, verbatim, kept in the AI changes log; required with confirm=true'),
        ];
    }
}

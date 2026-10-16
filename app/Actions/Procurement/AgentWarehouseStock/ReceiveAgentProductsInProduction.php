<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 14:55
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Procurement\AgentWarehouseStock;

use App\Actions\OrgAction;
use App\Actions\Procurement\AgentWarehouseStock\UI\IndexAgentProductsInProduction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Enums\Procurement\AgentWarehouseStock\AgentWarehouseStockStateEnum;
use App\Models\Procurement\AgentWarehouseStock;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

/**
 * Books goods the agent received from a supplier against the purchase order lines still in production: each line
 * becomes warehouse stock, received and not packed yet, and leaves the In production page once received in full.
 */
class ReceiveAgentProductsInProduction extends OrgAction
{
    use WithAgentOrganisation;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo("procurement.{$this->organisation->id}.edit");
    }

    /**
     * @param  list<array{purchase_order_transaction_id: int, quantity: float}>  $lines
     *
     * @return array{lines: int, units: float}
     */
    public function handle(Agent $agent, array $lines, ?string $notes = null): array
    {
        $quantities = collect($lines)
            ->groupBy('purchase_order_transaction_id')
            ->map(fn ($group) => round($group->sum('quantity'), 3));

        return DB::transaction(function () use ($agent, $quantities, $notes) {
            PurchaseOrderTransaction::whereIn('id', $quantities->keys())->orderBy('id')->lockForUpdate()->pluck('id');

            $inProduction = IndexAgentProductsInProduction::inProductionLines($agent)
                ->whereIn('purchase_order_transactions.id', $quantities->keys())
                ->select([
                    'purchase_order_transactions.id',
                    'purchase_order_transactions.organisation_id',
                    'purchase_order_transactions.supplier_product_id',
                    'purchase_order_transactions.purchase_order_id',
                    DB::raw(IndexAgentProductsInProduction::REMAINING_QUANTITY_SQL.' as remaining'),
                ])
                ->get()
                ->keyBy('id');

            $errors = [];
            foreach ($quantities as $transactionId => $quantity) {
                $line = $inProduction->get($transactionId);
                if (!$line) {
                    $errors["lines.$transactionId"] = __('This line is no longer in production');
                } elseif ($quantity > (float) $line->remaining) {
                    $errors["lines.$transactionId"] = __('Only :quantity units of this line are still in production', ['quantity' => (float) $line->remaining]);
                }
            }
            if ($errors) {
                throw ValidationException::withMessages($errors);
            }

            AgentWarehouseStock::insert($quantities->map(fn (float $quantity, int $transactionId) => [
                'group_id'                      => $agent->group_id,
                'agent_id'                      => $agent->id,
                'organisation_id'               => $inProduction[$transactionId]->organisation_id,
                'supplier_product_id'           => $inProduction[$transactionId]->supplier_product_id,
                'purchase_order_id'             => $inProduction[$transactionId]->purchase_order_id,
                'purchase_order_transaction_id' => $transactionId,
                'state'                         => AgentWarehouseStockStateEnum::RECEIVED->value,
                'quantity'                      => $quantity,
                'notes'                         => $notes,
                'created_at'                    => now(),
                'updated_at'                    => now(),
            ])->values()->all());

            return ['lines' => $quantities->count(), 'units' => (float) $quantities->sum()];
        });
    }

    public function rules(): array
    {
        return [
            'lines'                                 => ['required', 'array', 'min:1'],
            'lines.*.purchase_order_transaction_id' => ['required', 'integer'],
            'lines.*.quantity'                      => ['required', 'numeric', 'gt:0'],
            'notes'                                 => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    public function asController(Organisation $organisation, ActionRequest $request): array
    {
        $agent = $this->getOrganisationAgent($organisation);
        abort_unless($agent, 404);
        $this->initialisation($organisation, $request);

        return $this->handle($agent, $this->validatedData['lines'], $this->validatedData['notes'] ?? null);
    }

    public function action(Agent $agent, array $lines, ?string $notes = null): array
    {
        $this->asAction = true;
        $this->initialisation($agent->organisation, ['lines' => $lines, 'notes' => $notes]);

        return $this->handle($agent, $this->validatedData['lines'], $this->validatedData['notes'] ?? null);
    }

    /**
     * @param  array{lines: int, units: float}  $result
     */
    public function htmlResponse(array $result): RedirectResponse
    {
        return Redirect::back()->with('notification', [
            'status'  => 'success',
            'title'   => __('Received'),
            'message' => __(':lines lines, :units units moved to Received', ['lines' => $result['lines'], 'units' => $result['units']]),
        ]);
    }
}

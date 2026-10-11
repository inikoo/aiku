<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PrepareAgentPurchaseOrders extends OrgAction
{
    use WithProcurementEditAuthorisation;

    public const array ORDER_BUCKETS = ['out', 'w1', 'w2', 'w3'];
    public const array DEFAULT_ORDER_BUCKETS = ['out', 'w1', 'w2'];
    private const int DAYS_AFTER_ARRIVAL = 30;

    /**
     * Ordering from an agent, one click: what we are out of or about to run out of, worst first, in
     * whole cartons, goes onto a draft purchase order per sub-supplier. A sub-supplier's open draft is
     * reused, so it can be run again and again to stack more before anyone submits; the agent starts
     * work only on a submitted order. A budget (in our currency, for the lines added this time) skips
     * a line that would go over it and lets the next, cheaper ones still get their chance.
     *
     * @param  array<int, string>  $buckets
     * @return array{added: int, purchase_orders: int, skipped: int}
     *
     * @throws ValidationException
     */
    public function handle(OrgAgent $orgAgent, ?float $budget = null, array $buckets = self::DEFAULT_ORDER_BUCKETS, bool $worstOnly = true): array
    {
        $fail = fn (string $message) => throw ValidationException::withMessages(['rescue' => $message]);

        return DB::transaction(function () use ($orgAgent, $budget, $buckets, $worstOnly, $fail) {
            OrgAgent::whereKey($orgAgent->id)->lockForUpdate()->first();

            $lines = collect($this->linesByBucket($orgAgent))
                ->only($buckets)
                ->flatten(1)
                ->filter(fn (array $line) => !$worstOnly || $line['in_order']);

            if ($lines->isEmpty()) {
                $fail(__('Nothing to order from :agent right now', ['agent' => $orgAgent->agent->name]));
            }

            $orgSupplierProducts = OrgSupplierProduct::where('org_agent_id', $orgAgent->id)
                ->whereIn('id', $lines->pluck('org_supplier_product_id'))
                ->get()
                ->keyBy('id');

            $spent            = 0.0;
            $skippedForBudget = 0;
            $skipped          = 0;
            $purchaseOrderIds = [];

            foreach ($lines as $line) {
                if (!$orgSupplierProduct = $orgSupplierProducts->get($line['org_supplier_product_id'])) {
                    continue;
                }

                if ($budget !== null) {
                    if ($line['cost'] <= 0) {
                        continue;
                    }
                    if ($spent + $line['cost'] > $budget) {
                        $skippedForBudget++;

                        continue;
                    }
                }

                try {
                    $purchaseOrderIds[] = SetAgentDraftLine::make()->handle($orgAgent, $orgSupplierProduct, (float) $line['quantity_units'])->purchase_order_id;
                    $spent += $line['cost'];
                } catch (ValidationException|HttpException) {
                    $skipped++;
                }
            }

            if (!$purchaseOrderIds) {
                $fail($skippedForBudget
                    ? __('Nothing fits in a budget of :amount', ['amount' => $orgAgent->organisation->currency->code.' '.number_format($budget, 2)])
                    : __('Nothing to order from :agent right now', ['agent' => $orgAgent->agent->name]));
            }

            return [
                'added'           => count($purchaseOrderIds),
                'purchase_orders' => count(array_unique($purchaseOrderIds)),
                'skipped'         => $skipped,
            ];
        });
    }

    /**
     * Every line a prepared order could hold, per bucket, worst first. A line is "in order" when it
     * would run out before this order lands and a month after: already out, a best seller, never
     * forecast, or cover shorter than its own lead time plus that month.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function linesByBucket(OrgAgent $orgAgent): array
    {
        return collect(self::ORDER_BUCKETS)->mapWithKeys(fn (string $bucket) => [
            $bucket => array_map(fn (array $line) => [
                ...$line,
                'in_order' => $bucket === 'out'
                    || in_array($line['health_rank'], ['A', 'B'], true)
                    || $line['days_of_cover'] === null
                    || $line['days_of_cover'] < $line['lead_time_days'] + self::DAYS_AFTER_ARRIVAL,
            ], SuggestAgentShoppingList::make()->lines($orgAgent, $bucket)),
        ])->all();
    }

    /**
     * What the Prepare order panel shows before anything is created, from the same lines handle() uses.
     *
     * @return array<int, array{bucket: string, label: string, count: int, cost: float, order_lines: int, order_cost: float, cheapest: float, order_cheapest: float, cbm: float, order_cbm: float, cbm_estimated_lines: int, order_cbm_estimated_lines: int, left_out: object}>
     */
    public function summary(OrgAgent $orgAgent): array
    {
        return collect($this->linesByBucket($orgAgent))->map(function (array $lines, string $bucket) {
            $all     = collect($lines);
            $inOrder = $all->where('in_order', true);

            return [
                'bucket'         => $bucket,
                'label'          => GetAgentStockCoverBuckets::make()->bucketLabel($bucket),
                'count'          => $all->count(),
                'cost'           => round($all->sum('cost'), 2),
                'order_lines'    => $inOrder->count(),
                'order_cost'     => round($inOrder->sum('cost'), 2),
                'cheapest'       => (float) ($all->where('cost', '>', 0)->min('cost') ?? 0),
                'order_cheapest' => (float) ($inOrder->where('cost', '>', 0)->min('cost') ?? 0),
                'cbm'            => round($all->sum('cbm'), 2),
                'order_cbm'      => round($inOrder->sum('cbm'), 2),
                'cbm_estimated_lines'       => $all->where('cbm_estimated', true)->count(),
                'order_cbm_estimated_lines' => $inOrder->where('cbm_estimated', true)->count(),
                'left_out'       => (object) [],
            ];
        })->values()->all();
    }

    public function rules(): array
    {
        return [
            'budget'     => ['sometimes', 'nullable', 'numeric', 'gt:0'],
            'buckets'    => ['sometimes', 'array', 'min:1'],
            'buckets.*'  => ['string', Rule::in(self::ORDER_BUCKETS)],
            'worst_only' => ['sometimes', 'boolean'],
        ];
    }

    public function asController(Organisation $organisation, OrgAgent $orgAgent, ActionRequest $request): array
    {
        $this->initialisation($organisation, $request);

        $budget = $this->validatedData['budget'] ?? null;

        return $this->handle(
            $orgAgent,
            $budget === null ? null : (float) $budget,
            $this->validatedData['buckets'] ?? self::DEFAULT_ORDER_BUCKETS,
            (bool) ($this->validatedData['worst_only'] ?? true)
        );
    }

    public function htmlResponse(array $result, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('grp.org.procurement.org_agents.show.shopping.drafts.index', [
            $request->route('organisation')->slug,
            $request->route('orgAgent')->slug,
        ])->with('notification', [
            'status'  => $result['skipped'] ? 'warning' : 'success',
            'title'   => __('Draft purchase orders'),
            'message' => __(':lines lines on :orders draft purchase orders, :skipped could not be placed', [
                'lines'   => $result['added'],
                'orders'  => $result['purchase_orders'],
                'skipped' => $result['skipped'],
            ]),
        ]);
    }
}

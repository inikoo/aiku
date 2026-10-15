<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 13:45:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierAgreedTerm;

use App\Actions\OrgAction;
use App\Actions\Procurement\ProcurementNote\StoreProcurementNote;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteCategoryEnum;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteSeverityEnum;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteVisibilityEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\Procurement\PurchaseOrder;
use Spatie\Permission\Models\Permission;
use App\Models\SysAdmin\User;
use App\Notifications\AgreedTermDifferenceAcceptedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\ActionRequest;

/**
 * Purchasing staff accept, one by one and each with a reason, what a purchase order or a supplier's invoice
 * charges beyond what was agreed. Every accepted difference is an open incident on the supplier or agent, and
 * the procurement supervisor and the buyers of the order are told.
 */
class AcceptAgreedTermDifferences extends OrgAction
{
    private PurchaseOrder|StockDelivery $document;

    /**
     * @param  array{reasons: array<string, string>}  $modelData
     */
    public function handle(PurchaseOrder|StockDelivery $document, User $user, array $modelData): PurchaseOrder|StockDelivery
    {
        $check   = CheckAgainstAgreedTerms::make();
        $flags   = collect($document instanceof PurchaseOrder ? $check->purchaseOrder($document) : $check->stockDelivery($document))
            ->filter(fn (array $flag) => !$flag['accepted'] && filled(Arr::get($modelData, 'reasons.'.$flag['key'])));
        $subject = $document instanceof PurchaseOrder ? GetPurchaseOrderAgreedTerms::make()->subject($document) : $check->subjectOf($document);

        if ($flags->isEmpty()) {
            return $document;
        }

        DB::transaction(function () use ($document, $user, $modelData, $flags, $subject) {
            $data = $document->data ?? [];

            foreach ($flags as $flag) {
                $reason = trim($modelData['reasons'][$flag['key']]);

                data_set($data, CheckAgainstAgreedTerms::ACCEPTED.'.'.$flag['key'], [
                    'reason'  => $reason,
                    'user_id' => $user->id,
                    'by'      => $user->contact_name ?: $user->username,
                    'at'      => now()->toIso8601String(),
                ]);

                if ($subject) {
                    StoreProcurementNote::make()->action($subject, array_filter([
                        'category'             => ProcurementNoteCategoryEnum::INCIDENT->value,
                        'severity'             => ProcurementNoteSeverityEnum::WARNING->value,
                        'visibility'           => ProcurementNoteVisibilityEnum::INTERNAL->value,
                        'organisation_id'      => $document->organisation_id,
                        'purchase_order_id'    => $document instanceof PurchaseOrder ? $document->id : $document->purchaseOrders()->value('purchase_orders.id'),
                        'title'                => __(':document: :title accepted against the agreed terms', ['document' => $document->reference, 'title' => $flag['title']]),
                        'body'                 => __('Found :found, agreed :agreed. Accepted by :user: :reason', [
                            'found'  => $flag['found'],
                            'agreed' => $flag['agreed'],
                            'user'   => $user->contact_name ?: $user->username,
                            'reason' => $reason,
                        ]),
                        'incident_amount'      => $flag['amount'] !== null ? abs($flag['amount']) : null,
                        'incident_currency_id' => $flag['amount'] !== null ? $flag['currency_id'] : null,
                        'author_user_id'       => $user->id,
                    ], fn ($value) => $value !== null), strict: false);
                }
            }

            $document->update(['data' => $data]);
        });

        $this->notify($document, $user, $flags->pluck('title')->implode(', '));

        return $document;
    }

    private function notify(PurchaseOrder|StockDelivery $document, User $user, string $titles): void
    {
        $permissions = Permission::where('name', "org-supervisor.$document->organisation_id.procurement")->pluck('name')->all();
        $buyerIds    = $document instanceof PurchaseOrder ? [$document->buyer_id] : $document->purchaseOrders()->pluck('buyer_id')->all();

        $recipients = User::where('status', true)
            ->where('id', '!=', $user->id)
            ->where(fn ($query) => $query
                ->whereIn('id', array_filter($buyerIds))
                ->when($permissions, fn ($query) => $query->orWhereIn('id', User::permission($permissions)->select('users.id'))))
            ->get();

        Notification::send($recipients, new AgreedTermDifferenceAcceptedNotification($document, $user->contact_name ?: $user->username, $titles));
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo("procurement.{$this->document->organisation_id}.edit");
    }

    public function rules(): array
    {
        return [
            'reasons'   => ['required', 'array', 'min:1'],
            'reasons.*' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function inPurchaseOrder(PurchaseOrder $purchaseOrder, ActionRequest $request): PurchaseOrder
    {
        $this->document = $purchaseOrder;
        $this->initialisation($purchaseOrder->organisation, $request);

        return $this->handle($purchaseOrder, $request->user(), $this->validatedData);
    }

    public function inStockDelivery(StockDelivery $stockDelivery, ActionRequest $request): StockDelivery
    {
        $this->document = $stockDelivery;
        $this->initialisation($stockDelivery->organisation, $request);

        return $this->handle($stockDelivery, $request->user(), $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}

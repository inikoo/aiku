<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent;

use App\Actions\OrgAction;
use App\Actions\Procurement\OrgAgent\UI\ShowAgentDraftsToSend;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use App\Notifications\SupplierDraftSharedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Spatie\Permission\Models\Permission;

class TellOrganisationsAboutSupplierDraft extends OrgAction
{
    use WithProcurementEditAuthorisation;

    private const int HOURS_BETWEEN_ASKS = 24;

    /**
     * A courtesy, never a gate: the buyers of the other organisations that order through this agent
     * are told a supplier is about to be ordered from, so they can stack their own products on it.
     * The organisation that asked can still send whenever it likes.
     *
     * @return int buyers told
     *
     * @throws ValidationException
     */
    public function handle(OrgAgent $orgAgent, Supplier $supplier, User $user): int
    {
        $fail = fn (string $message) => throw ValidationException::withMessages(['drafts' => $message]);

        $draft = PurchaseOrder::whereIn('id', ShowAgentDraftsToSend::drafts($orgAgent)->where('po.supplier_id', $supplier->id)->pluck('po.id'))
            ->where('organisation_id', $orgAgent->organisation_id)
            ->first();

        if (!$draft) {
            $fail(__('Your organisation has no draft for this supplier'));
        }

        $askedAt = $draft->data['others_asked']['at'] ?? null;
        if ($askedAt && Carbon::parse($askedAt)->gt(now()->subHours(self::HOURS_BETWEEN_ASKS))) {
            $fail(__('The other organisations were already told today'));
        }

        $by   = $user->contact_name ?: $user->username;
        $told = 0;

        $otherOrgAgents = OrgAgent::where('agent_id', $orgAgent->agent_id)
            ->where('group_id', $orgAgent->group_id)
            ->where('organisation_id', '!=', $orgAgent->organisation_id)
            ->with('organisation')
            ->get();

        foreach ($otherOrgAgents as $otherOrgAgent) {
            $permissions = Permission::where('name', "procurement.$otherOrgAgent->organisation_id.edit")->pluck('name')->all();
            if (!$permissions) {
                continue;
            }

            $recipients = User::permission($permissions)->where('status', true)->where('id', '!=', $user->id)->get();

            Notification::send($recipients, new SupplierDraftSharedNotification(
                $otherOrgAgent,
                __(':organisation is about to order from :supplier', ['organisation' => $orgAgent->organisation->code, 'supplier' => $supplier->code]),
                __(':user is preparing an order to :supplier through :agent. If :yours needs anything from them, add it to a draft now so it travels together.', [
                    'user'     => $by,
                    'supplier' => $supplier->code,
                    'agent'    => $orgAgent->agent->name,
                    'yours'    => $otherOrgAgent->organisation->code,
                ])
            ));

            $told += $recipients->count();
        }

        $draft->update(['data' => [...($draft->data ?? []), 'others_asked' => ['by' => $by, 'at' => now()->toIso8601String(), 'told' => $told]]]);

        return $told;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
        ];
    }

    public function asController(Organisation $organisation, OrgAgent $orgAgent, ActionRequest $request): int
    {
        $this->initialisation($organisation, $request);

        return $this->handle($orgAgent, Supplier::findOrFail($this->validatedData['supplier_id']), $request->user());
    }

    public function htmlResponse(int $told): RedirectResponse
    {
        return Redirect::back()->with('notification', [
            'status'  => $told ? 'success' : 'warning',
            'title'   => __('Other organisations'),
            'message' => $told
                ? __(':count buyers in the other organisations have been told', ['count' => $told])
                : __('No buyer in another organisation orders through this agent'),
        ]);
    }
}

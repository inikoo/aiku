<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierShareLink;

use App\Actions\OrgAction;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierShareLink;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

/**
 * Makes a private link for an independent supplier. A supplier behind an agent gets none: our cost for its products
 * is what we pay the agent, so it would show the factory the agent's margin.
 */
class StoreSupplierShareLink extends OrgAction
{
    public const array MONTHS = [1, 3, 6, 12];

    /**
     * @param  array{months: int, email?: string|null, created_by_user_id?: int|null}  $modelData
     * @return array{link: SupplierShareLink, url: string}
     */
    public function handle(Supplier $supplier, array $modelData): array
    {
        abort_if($supplier->agent_id, 422, __('A supplier behind an agent is followed by its agent'));

        $token = Str::random(48);

        $link = SupplierShareLink::create([
            'group_id'           => $supplier->group_id,
            'supplier_id'        => $supplier->id,
            'token_hash'         => SupplierShareLink::hashToken($token),
            'email'              => $modelData['email'] ?? null,
            'expires_at'         => now()->addMonthsNoOverflow((int) $modelData['months']),
            'created_by_user_id' => $modelData['created_by_user_id'] ?? null,
        ]);

        return ['link' => $link, 'url' => route('grp.supplier_share.show', $token)];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo('supply-chain.edit');
    }

    public function rules(): array
    {
        return [
            'months' => ['required', 'integer', Rule::in(self::MONTHS)],
            'email'  => ['sometimes', 'nullable', 'email:rfc', 'max:255'],
        ];
    }

    /**
     * @return array{link: SupplierShareLink, url: string}
     */
    public function asController(Supplier $supplier, ActionRequest $request): array
    {
        $this->initialisationFromGroup($supplier->group, $request);

        return $this->handle($supplier, [...$this->validatedData, 'created_by_user_id' => $request->user()->id]);
    }

    /**
     * @param  array{link: SupplierShareLink, url: string}  $result
     * @return array{url: string}
     */
    public function jsonResponse(array $result): array
    {
        return ['url' => $result['url']];
    }
}

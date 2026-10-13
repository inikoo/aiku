<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Oct 2026, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithGoodsInBookInAuthorisation;
use App\Enums\Inventory\OrgStock\OrgStockDiscontinueSuggestionReasonEnum;
use App\Enums\Inventory\OrgStock\OrgStockDiscontinueSuggestionSourceEnum;
use App\Models\Inventory\OrgStock;
use App\Models\Inventory\OrgStockDiscontinueSuggestion;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

/**
 * Logs a suggestion to discontinue a product from the people who meet it: buyers, customer services
 * and goods in. It never changes the state of the stock: DiscontinueOrgStocks stays the only door
 * for that, and the people who hold it read the tally.
 */
class SuggestOrgStockDiscontinue extends OrgAction
{
    use WithGoodsInBookInAuthorisation;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->authToBookIn($request, 'incoming.%d.edit')) {
            return true;
        }

        $shopPermissions = $this->organisation->shops()->pluck('id')->map(fn (int $shopId) => "crm.$shopId.edit")->all();

        return $shopPermissions && $request->user()->authTo($shopPermissions);
    }

    public function handle(OrgStock $orgStock, User $user, array $modelData): OrgStockDiscontinueSuggestion
    {
        return OrgStockDiscontinueSuggestion::create([
            'group_id'        => $orgStock->group_id,
            'organisation_id' => $orgStock->organisation_id,
            'org_stock_id'    => $orgStock->id,
            'stock_id'        => $orgStock->stock_id,
            'user_id'         => $user->id,
            'source'          => $modelData['source'],
            'reasons'         => array_values(array_unique($modelData['reasons'])),
            'note'            => trim($modelData['note']),
        ]);
    }

    public function rules(): array
    {
        return [
            'source'    => ['required', Rule::enum(OrgStockDiscontinueSuggestionSourceEnum::class)],
            'reasons'   => ['required', 'array', 'min:1'],
            'reasons.*' => [Rule::enum(OrgStockDiscontinueSuggestionReasonEnum::class)],
            'note'      => ['required', 'string', 'min:3', 'max:2000'],
        ];
    }

    public function asController(OrgStock $orgStock, ActionRequest $request): RedirectResponse
    {
        $this->initialisation($orgStock->organisation, $request);
        $this->handle($orgStock, $request->user(), $this->validatedData);

        return back();
    }

    public function action(OrgStock $orgStock, User $user, array $modelData): OrgStockDiscontinueSuggestion
    {
        $this->asAction = true;
        $this->initialisation($orgStock->organisation, $modelData);

        return $this->handle($orgStock, $user, $this->validatedData);
    }
}

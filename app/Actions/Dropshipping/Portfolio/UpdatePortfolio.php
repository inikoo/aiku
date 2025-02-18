<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 19:36:21 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Portfolio;

use App\Actions\CRM\Customer\Hydrators\CustomerHydratePortfolios;
use App\Actions\Dropshipping\Portfolio\Hydrators\ShopHydratePortfolios;
use App\Actions\Dropshipping\Portfolio\Search\PortfolioRecordSearch;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydratePortfolios;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePortfolios;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dropshipping\Portfolio;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdatePortfolio extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;


    private Portfolio $portfolio;

    public function handle(Portfolio $portfolio, array $modelData): Portfolio
    {
        $portfolio = $this->update($portfolio, $modelData, ['data']);


        if ($portfolio->wasChanged(['status'])) {
            GroupHydratePortfolios::dispatch($portfolio->group)->delay($this->hydratorsDelay);
            OrganisationHydratePortfolios::dispatch($portfolio->organisation)->delay($this->hydratorsDelay);
            ShopHydratePortfolios::dispatch($portfolio->shop)->delay($this->hydratorsDelay);
            CustomerHydratePortfolios::dispatch($portfolio->shop)->delay($this->hydratorsDelay);
        }

        PortfolioRecordSearch::dispatch($portfolio);

        return $portfolio;
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo("crm.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        $rules = [
            'reference'       => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                new IUnique(
                    table: 'portfolios',
                    extraConditions: [
                        ['column' => 'customer_id', 'value' => $this->shop->id],
                        ['column' => 'status', 'value' => true],
                        ['column' => 'id', 'value' => $this->portfolio->id, 'operator' => '!='],
                    ]
                ),
            ],
            'status'          => 'sometimes|boolean',
            'last_added_at'   => 'sometimes|date',
            'last_removed_at' => 'sometimes|date',
        ];

        if (!$this->strict) {
            $rules = $this->noStrictUpdateRules($rules);
        }

        return $rules;
    }


    public function action(Portfolio $portfolio, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): Portfolio
    {
        $this->strict = $strict;
        if (!$audit) {
            Portfolio::disableAuditing();
        }
        $this->asAction       = true;
        $this->portfolio      = $portfolio;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($portfolio->shop, $modelData);

        return $this->handle($portfolio, $this->validatedData);
    }


}

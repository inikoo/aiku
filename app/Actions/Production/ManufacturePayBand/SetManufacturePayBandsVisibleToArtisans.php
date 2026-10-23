<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 20:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\ManufacturePayBand;

use App\Actions\OrgAction;
use App\Actions\Production\Production\UI\ShowArtisansDashboard;
use App\Models\Production\Production;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

/**
 * The Production Manager chooses whether artisans see their pay band on the manufacture floor tablet.
 * Bands are still worked out and paid either way.
 */
class SetManufacturePayBandsVisibleToArtisans extends OrgAction
{
    public function handle(Production $production, array $modelData): Production
    {
        $production->update([
            'settings' => array_merge($production->settings ?? [], [
                'show_pay_bands_to_artisans' => (bool) $modelData['show_pay_bands_to_artisans'],
            ]),
        ]);

        return $production;
    }

    public function rules(): array
    {
        return [
            'show_pay_bands_to_artisans' => ['required', 'boolean'],
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return ShowArtisansDashboard::canSeeArtisanPay($request->user(), $this->production);
    }

    public function action(Production $production, array $modelData): Production
    {
        $this->asAction = true;
        $this->initialisationFromProduction($production, $modelData);

        return $this->handle($production, $this->validatedData);
    }

    public function asController(Organisation $organisation, Production $production, ActionRequest $request): Production
    {
        $this->initialisationFromProduction($production, $request);

        return $this->handle($production, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}

<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 20:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\ManufactureTaskSession;

use App\Actions\OrgAction;
use App\Actions\Production\Production\UI\ShowArtisansDashboard;
use App\Models\Production\Production;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

/**
 * The Production Manager sets how short a Preparation is added to the job step started straight after it; 0 keeps every Preparation.
 */
class SetManufactureShortPreparationMinutes extends OrgAction
{
    public function handle(Production $production, array $modelData): Production
    {
        $production->update([
            'settings' => array_merge($production->settings ?? [], [
                'short_preparation_minutes' => (int) $modelData['minutes'],
            ]),
        ]);

        return $production;
    }

    public function rules(): array
    {
        return [
            'minutes' => ['required', 'integer', 'min:0', 'max:60'],
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

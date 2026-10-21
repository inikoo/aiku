<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 16:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\ManufactureArtisanDay;

use App\Actions\OrgAction;
use App\Actions\Production\Production\UI\ShowArtisansDashboard;
use App\Models\Production\ManufactureHoursTolerance;
use App\Models\Production\Production;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

/**
 * The Production Manager or the accountant sets the tolerance from a date; days before it keep the tolerance they had.
 */
class SetManufactureHoursTolerance extends OrgAction
{
    public function handle(Production $production, User $user, array $modelData): ManufactureHoursTolerance
    {
        return ManufactureHoursTolerance::updateOrCreate(
            [
                'production_id'  => $production->id,
                'effective_from' => $modelData['effective_from'],
            ],
            [
                'group_id'          => $production->group_id,
                'organisation_id'   => $production->organisation_id,
                'tolerance_minutes' => $modelData['tolerance_minutes'],
                'set_by_user_id'    => $user->id,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'tolerance_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'effective_from'    => ['required', 'date_format:Y-m-d'],
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return ShowArtisansDashboard::canSeeArtisanPay($request->user(), $this->production);
    }

    public function action(Production $production, User $user, array $modelData): ManufactureHoursTolerance
    {
        $this->asAction = true;
        $this->initialisationFromProduction($production, $modelData);

        return $this->handle($production, $user, $this->validatedData);
    }

    public function asController(Organisation $organisation, Production $production, ActionRequest $request): ManufactureHoursTolerance
    {
        $this->initialisationFromProduction($production, $request);

        return $this->handle($production, $request->user(), $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}

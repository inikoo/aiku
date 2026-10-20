<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\Packaging;

use App\Actions\OrgAction;
use App\Enums\Goods\Packaging\EprSchemeEnum;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

/**
 * Records the organisation's registration with a country's packaging scheme (register number and the scheme or
 * compliance operator it is contracted with), or removes it when no number is given.
 */
class SetOrganisationEprRegistration extends OrgAction
{
    public function handle(Organisation $organisation, array $modelData): Organisation
    {
        $settings      = $organisation->settings ?? [];
        $registrations = $settings['epr_registrations'] ?? [];
        $countryCode   = $modelData['country_code'];

        if (blank($modelData['registration_number'] ?? null)) {
            unset($registrations[$countryCode]);
        } else {
            $registrations[$countryCode] = [
                'registration_number' => $modelData['registration_number'],
                'scheme_operator'     => $modelData['scheme_operator'] ?? null,
                'registered_on'       => $modelData['registered_on'] ?? null,
                'recorded_by'         => $modelData['recorded_by'] ?? null,
                'recorded_at'         => now()->toDateString(),
            ];
        }
        $settings['epr_registrations'] = $registrations;
        $organisation->update(['settings' => $settings]);

        return $organisation;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo('org-reports.'.$this->organisation->id);
    }

    public function rules(): array
    {
        return [
            'country_code'        => ['required', Rule::in(EprSchemeEnum::COUNTRIES_WITH_SCHEMES)],
            'registration_number' => ['nullable', 'string', 'max:64'],
            'scheme_operator'     => ['nullable', 'string', 'max:128'],
            'registered_on'       => ['nullable', 'date'],
        ];
    }

    public function asController(Organisation $organisation, ActionRequest $request): Organisation
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation, [...$this->validatedData, 'recorded_by' => $request->user()->contact_name]);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}

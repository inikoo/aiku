<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\Fulfilment;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateFulfilment extends OrgAction
{
    use WithActionUpdate;


    protected Fulfilment $fulfilment;

    public function handle(Fulfilment $fulfilment, array $modelData): Fulfilment
    {


        $settings       = $fulfilment->settings;
        $updateSettings = false;

        if(Arr::exists($modelData, 'weekly_cut_off_day')) {
            $settings['rental_agreement_cut_off']['weekly']['day'] = $modelData['weekly_cut_off_day'];
            $updateSettings                                        = true;
            data_forget($modelData, 'weekly_cut_off_day');
        }

        if(Arr::exists($modelData, 'monthly_cut_off')) {
            $settings['rental_agreement_cut_off']['monthly']['day'] = $modelData['monthly_cut_off']['date'];
            $settings['rental_agreement_cut_off']['monthly']['workdays'] = $modelData['monthly_cut_off']['isWeekdays'];
            $updateSettings = true;
            data_forget($modelData, 'monthly_cut_off');
            // data_forget($modelData, 'monthly_only_weekdays');
        }

        if($updateSettings) {
            $modelData['settings'] = $settings;
        }



        return $this->update($fulfilment, $modelData, ['settings']);


    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'weekly_cut_off_day'     => ['sometimes','string', Rule::in(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']) ],
            'monthly_cut_off' => [
                'sometimes',
                'array'
            ],
            'monthly_cut_off.date' => [
                'sometimes',
                'integer',
                'min:1',
                'max:31'
            ],
            'monthly_cut_off.isWeekdays' => [
                'sometimes',
                'boolean',
            ]
        ];
    }

    public function asController(Fulfilment $fulfilment, ActionRequest $request): Fulfilment
    {

        $this->fulfilment = $fulfilment;

        $this->initialisationFromFulfilment($fulfilment, $request);
        return $this->handle($fulfilment, $this->validatedData);
    }

    public function action(Fulfilment $fulfilment, array $modelData, int $hydratorsDelay = 0): Fulfilment
    {
        $this->fulfilment     = $fulfilment;
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromFulfilment($fulfilment, $modelData);

        return $this->handle($fulfilment, $this->validatedData);
    }
}

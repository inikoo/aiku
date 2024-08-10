<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:45:00 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect;

use App\Actions\CRM\Prospect\Search\ProspectRecordSearch;
use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithProspectPrepareForValidation;
use App\Enums\CRM\Prospect\ProspectContactedStateEnum;
use App\Enums\CRM\Prospect\ProspectFailStatusEnum;
use App\Enums\CRM\Prospect\ProspectSuccessStatusEnum;
use App\Http\Resources\Lead\ProspectResource;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Prospect;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use App\Rules\Phone;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateProspect extends OrgAction
{
    use WithActionUpdate;
    use WithProspectPrepareForValidation;

    private Prospect $prospect;

    public function handle(Prospect $prospect, array $modelData): Prospect
    {
        $addressData = Arr::get($modelData, 'address');
        Arr::forget($modelData, 'address');

        if (Arr::has($modelData, 'email')) {
            $isValidEmail = true;
            if (Arr::get($modelData, 'email', '') != '' && !filter_var(Arr::get($modelData, 'email'), FILTER_VALIDATE_EMAIL)) {
                $isValidEmail = false;
            }
            data_set($modelData, 'is_valid_email', $isValidEmail);
        }

        $prospect = $this->update($prospect, $modelData, ['data']);

        if ($addressData) {
            UpdateAddress::run($prospect->address, $addressData);
            $prospect->update(
                [
                    'location' => $prospect->address->getLocation()
                ]
            );

        }

        ProspectRecordSearch::dispatch($prospect);

        return $prospect;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        $rules = [
            'contacted_state'   => ['sometimes', Rule::enum(ProspectContactedStateEnum::class)],
            'fail_status'       => ['sometimes', 'nullable', Rule::enum(ProspectFailStatusEnum::class)],
            'success_status'    => ['sometimes', 'nullable', Rule::enum(ProspectSuccessStatusEnum::class)],
            'dont_contact_me'   => ['sometimes', 'boolean'],
            'last_contacted_at' => 'sometimes|nullable|date',
            'contact_name'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_name'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'address'           => ['sometimes', 'nullable', new ValidAddress()],
            'email'             => [
                'sometimes',
                'string:500',
                new IUnique(
                    table: 'prospects',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'id', 'operator' => '!=', 'value' => $this->prospect->id]

                    ]
                ),

            ],
            'phone'             => [
                'sometimes',
                'nullable',
                'string',
                'max:24',
            ],
            'contact_website'   => [
                'sometimes',
                'nullable',
                'string',
                'max:500',
            ],
        ];

        if ($this->strict) {
            $strictRules = [
                'email'           => [
                    'sometimes',
                    'email',
                    'max:500',
                    new IUnique(
                        table: 'prospects',
                        extraConditions: [
                            ['column' => 'shop_id', 'value' => $this->shop->id],
                            ['column' => 'id', 'operator' => '!=', 'value' => $this->prospect->id]

                        ]
                    ),

                ],
                'phone'           => [
                    'sometimes',
                    'nullable',
                    new Phone(),
                    new IUnique(
                        table: 'prospects',
                        extraConditions: [
                            ['column' => 'shop_id', 'value' => $this->shop->id],
                            ['column' => 'id', 'operator' => '!=', 'value' => $this->prospect->id]

                        ]
                    ),
                ],
                'contact_website' => [
                    'sometimes',
                    'nullable',
                    'url:http,https',
                    new IUnique(
                        table: 'prospects',
                        extraConditions: [
                            ['column' => 'shop_id', 'value' => $this->shop->id],
                            ['column' => 'id', 'operator' => '!=', 'value' => $this->prospect->id]

                        ]
                    ),
                ],
            ];
            $rules       = array_merge($rules, $strictRules);
        }

        return $rules;
    }

    public function asController(Organisation $organisation, Shop $shop, Prospect $prospect, ActionRequest $request): Prospect
    {
        $this->initialisationFromShop($prospect->shop, $request);
        $this->prospect = $prospect;

        return $this->handle($prospect, $this->validatedData);
    }

    public function action(Prospect $prospect, $modelData, int $hydratorsDelay = 0, bool $strict = true): Prospect
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = $strict;
        $this->prospect       = $prospect;
        $this->initialisationFromShop($prospect->shop, $modelData);

        return $this->handle($prospect, $this->validatedData);
    }

    public function jsonResponse(Prospect $prospect): ProspectResource
    {
        return new ProspectResource($prospect);
    }
}

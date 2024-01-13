<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:45:00 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect;

use App\Actions\CRM\Prospect\Hydrators\ProspectHydrateUniversalSearch;
use App\Actions\CRM\Prospect\Tags\SyncTagsProspect;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Helpers\Query\HydrateModelTypeQueries;
use App\Actions\Market\Shop\Hydrators\ShopHydrateProspects;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProspects;
use App\Actions\Traits\WithCheckCanContactByEmail;
use App\Actions\Traits\WithCheckCanContactByPhone;
use App\Actions\Traits\WithProspectPrepareForValidation;
use App\Enums\CRM\Prospect\ProspectContactedStateEnum;
use App\Enums\CRM\Prospect\ProspectFailStatusEnum;
use App\Enums\CRM\Prospect\ProspectStateEnum;
use App\Enums\CRM\Prospect\ProspectSuccessStatusEnum;
use App\Models\CRM\Prospect;
use App\Models\Market\Shop;
use App\Rules\IUnique;
use App\Rules\Phone;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreProspect extends OrgAction
{
    use WithAttributes;
    use WithProspectPrepareForValidation;
    use WithCheckCanContactByEmail;
    use WithCheckCanContactByPhone;

    private bool $asAction     = false;
    public int $hydratorsDelay = 0;
    private bool $strict       = true;

    public function handle(Shop $shop, array $modelData): Prospect
    {
        $tags = Arr::get($modelData, 'tags', []);
        Arr::forget($modelData, 'tags');

        $contactAddressData = Arr::get($modelData, 'contact_address');
        Arr::forget($modelData, 'contact_address');

        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'organisation_id', $shop->organisation_id);


        $isValidEmail = true;
        if (Arr::get($modelData, 'email', '') != '' && !filter_var(Arr::get($modelData, 'email'), FILTER_VALIDATE_EMAIL)) {
            $isValidEmail = false;
        }
        data_set($modelData, 'is_valid_email', $isValidEmail);


        if (
            !$isValidEmail and
            !Arr::has($modelData, 'phone')
            and (!Arr::has($modelData, 'state')
                or Arr::get($modelData, 'state') == ProspectStateEnum::NO_CONTACTED
            )
        ) {
            data_set($modelData, 'state', ProspectStateEnum::FAIL);
            data_set($modelData, 'fail_status', ProspectFailStatusEnum::INVALID);
        }


        /** @var Prospect $prospect */
        $prospect = $shop->prospects()->create($modelData);

        $prospect->can_contact_by_email = $this->canContactByEmail($prospect);
        $prospect->can_contact_by_phone = $this->canContactByPhone($prospect);

        $prospect->save();

        StoreAddressAttachToModel::run($prospect, $contactAddressData, ['scope' => 'contact']);
        $prospect->location = $prospect->getLocation();
        $prospect->save();

        ProspectHydrateUniversalSearch::dispatch($prospect);
        OrganisationHydrateProspects::dispatch($shop->organisation)->delay(now()->addSeconds(2));
        ShopHydrateProspects::dispatch($shop);

        HydrateModelTypeQueries::dispatch('Prospect')->delay(now()->addSeconds(2));

        if ($tags && count($tags)) {
            SyncTagsProspect::make()->action($prospect, ['tags' => $tags, 'type' => 'crm']);
        }

        return $prospect;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.{$this->shop->slug}.edit");
    }

    public function rules(): array
    {
        return [
            'contacted_state'   => ['sometimes', Rule::enum(ProspectContactedStateEnum::class)],
            'fail_status'       => ['sometimes', 'nullable', Rule::enum(ProspectFailStatusEnum::class)],
            'success_status'    => ['sometimes', 'nullable', Rule::enum(ProspectSuccessStatusEnum::class)],
            'dont_contact_me'   => ['sometimes', 'boolean'],
            'state'             => ['sometimes', new Enum(ProspectStateEnum::class)],
            'data'              => 'sometimes|array',
            'last_contacted_at' => 'sometimes|nullable|date',
            'created_at'        => 'sometimes|date',
            'address'           => ['sometimes', 'nullable', new ValidAddress()],
            'contact_name'      => ['nullable', 'string', 'max:255'],
            'company_name'      => ['nullable', 'string', 'max:255'],
            'tags'              => ['sometimes', 'nullable', 'array'],
            'tags.*'            => ['string'],
            'email'             => [
                'required_without:phone',
                'email',
                'max:500',
                new IUnique(
                    table: 'prospects',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],

                    ]
                ),

            ],
            'phone'             => [
                'required_without:email',
                'nullable',
                new Phone(),
                new IUnique(
                    table: 'prospects',
                    extraConditions:[
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                    ]
                ),
            ],
            'contact_website'   => [
                'nullable',
                'url:http,https'
            ],

        ];
    }

    public function action(Shop $shop, array $modelData, int $hydratorsDelay = 0, bool $strict = true): Prospect
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = $strict;
        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($shop, $this->validatedData);
    }

}

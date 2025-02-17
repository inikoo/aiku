<?php
/*
 * author Arya Permana - Kirin
 * created on 17-02-2025-16h-03m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\PaymentAccountShop;

use App\Actions\Accounting\PaymentAccount\Hydrators\PaymentAccountHydratePAS;
use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydrateStoredItems;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateStoredItems;
use App\Actions\Fulfilment\Pallet\Hydrators\PalletHydrateStoredItems;
use App\Actions\Fulfilment\Pallet\Hydrators\PalletHydrateWithStoredItems;
use App\Actions\Fulfilment\StoredItem\Search\StoredItemRecordSearch;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateStoredItems;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateStoredItems;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Accounting\PaymentAccountShop\PaymentAccountShopStateEnum;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentAccountShop;
use App\Models\Catalogue\Shop;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\StoredItem;
use App\Rules\AlphaDashDotSpaceSlashParenthesisPlus;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdatePaymentAccountShop extends OrgAction
{
    use WithActionUpdate;
    public function handle(PaymentAccountShop $paymentAccountShop, array $modelData): PaymentAccountShop
    {
        $paymentAccountShop = $this->update($paymentAccountShop, $modelData);

        PaymentAccountHydratePAS::dispatch($paymentAccountShop->paymentAccount);

        return $paymentAccountShop;
    }

    public function rules(): array
    {
        return [
            'state'              => [
                'sometimes',
                Rule::enum(PaymentAccountShopStateEnum::class)
            ],
            'currency_id' => [
                'sometimes',
                'nullable',
                Rule::Exists('currencies', 'id')
            ]
        ];
    }

    public function asController(PaymentAccountShop $paymentAccountShop, ActionRequest $request): PaymentAccountShop
    {
        $this->initialisation($paymentAccountShop->paymentAccount->organisation, $request);

        return $this->handle($paymentAccountShop, $this->validateAttributes());
    }

    public function action(PaymentAccountShop $paymentAccountShop, array $modelData): PaymentAccountShop
    {
        $this->asAction           = true;
        $this->initialisation($paymentAccountShop->paymentAccount->organisation, $modelData);

        return $this->handle($paymentAccountShop, $this->validateAttributes());
    }

}

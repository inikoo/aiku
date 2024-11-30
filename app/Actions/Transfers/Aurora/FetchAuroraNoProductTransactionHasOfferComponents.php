<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 20 Nov 2024 18:59:01 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Discounts\TransactionHasOfferComponent\StoreTransactionHasOfferComponent;
use App\Actions\Discounts\TransactionHasOfferComponent\UpdateTransactionHasOfferComponent;
use App\Models\Discounts\TransactionHasOfferComponent;
use App\Models\Ordering\Order;
use App\Transfers\Aurora\WithAuroraParsers;
use App\Transfers\SourceOrganisationService;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchAuroraNoProductTransactionHasOfferComponents
{
    use AsAction;
    use WithAuroraParsers;


    private SourceOrganisationService $organisationSource;

    public function handle(SourceOrganisationService $organisationSource, int $source_id, Order $order): ?TransactionHasOfferComponent
    {
        $this->organisationSource = $organisationSource;

        $transactionHasOfferComponentData = $organisationSource->fetchNoProductTransactionHasOfferComponent(id: $source_id, order: $order);
        if (!$transactionHasOfferComponentData) {
            return null;
        }


        $transactionHasOfferComponent      = TransactionHasOfferComponent::where('source_alt_id', $transactionHasOfferComponentData['transaction_has_offer_component']['source_alt_id'])->first();

        if ($transactionHasOfferComponent) {
            $transactionHasOfferComponent = UpdateTransactionHasOfferComponent::make()->action(
                transactionHasOfferComponent: $transactionHasOfferComponent,
                modelData: $transactionHasOfferComponentData,
                hydratorsDelay: 60,
                strict: false
            );
        }

        if (!$transactionHasOfferComponent) {
            $transactionHasOfferComponent = StoreTransactionHasOfferComponent::make()->action(
                transaction: $transactionHasOfferComponentData['transaction'],
                offerComponent: $transactionHasOfferComponentData['offer_component'],
                modelData: $transactionHasOfferComponentData['transaction_has_offer_component'],
                hydratorsDelay: 60,
                strict: false
            );
        }

        return $transactionHasOfferComponent;
    }



}

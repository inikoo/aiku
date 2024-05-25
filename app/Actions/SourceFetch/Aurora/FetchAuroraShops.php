<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 01:27:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Helpers\TaxNumber\DeleteTaxNumber;
use App\Actions\Helpers\TaxNumber\StoreTaxNumber;
use App\Actions\Helpers\TaxNumber\UpdateTaxNumber;
use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Models\Catalogue\Shop;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraShops extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:shops {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Shop
    {
        if ($shopData = $organisationSource->fetchShop($organisationSourceId)) {
            setPermissionsTeamId($organisationSource->getOrganisation()->group_id);


            if ($shop = Shop::where('source_id', $shopData['shop']['source_id'])
                ->first()) {
                $shop = UpdateShop::make()->action(
                    shop: $shop,
                    modelData: $shopData['shop']
                );


                if ($shopData['tax_number']) {
                    if (!$shop->taxNumber) {
                        StoreTaxNumber::run(
                            owner: $shop,
                            modelData: $shopData['tax_number']
                        );
                    } else {
                        UpdateTaxNumber::run($shop->taxNumber, $shopData['tax_number']);
                    }
                } elseif ($shop->taxNumber) {
                    DeleteTaxNumber::run($shop->taxNumber);

                }


                if (!empty($shopData['collectionAddress'])) {
                    if ($collectionAddress = $shop->getAddress('collection')) {
                        UpdateAddress::run($collectionAddress, $shopData['collectionAddress']);
                    } else {
                        StoreAddressAttachToModel::run($shop, $shopData['collectionAddress'], ['scope' => 'collection']);
                    }
                }
            } else {


                $shop = StoreShop::make()->action(
                    organisation: $organisationSource->getOrganisation(),
                    modelData: $shopData['shop']
                );

                if ($shopData['tax_number']) {
                    StoreTaxNumber::run(
                        owner: $shop,
                        modelData: $shopData['tax_number']
                    );
                }




                if (!empty($shopData['collectionAddress'])) {
                    StoreAddressAttachToModel::run($shop, $shopData['collectionAddress'], ['scope' => 'collection']);
                }
            }


            $shopSourceId = explode(':', $shop->source_id);

            $auroraOutboxes = DB::connection('aurora')->table('Email Campaign Type Dimension')
                ->where('Email Campaign Type Store Key', $shopSourceId[1])
                ->get()
                ->pluck('Email Campaign Type Key', 'Email Campaign Type Code')->all();


            foreach ($shop->outboxes as $outbox) {
                $sourceId = match ($outbox->type) {
                    'new-customer'               => $auroraOutboxes['New Customer'],
                    'abandoned-cart'             => $auroraOutboxes['AbandonedCart'],
                    'basket-low-stock'           => $auroraOutboxes['Basket Low Stock'] ?? null,
                    'basket-reminder1'           => $auroraOutboxes['Basket Reminder 1'],
                    'basket-reminder2'           => $auroraOutboxes['Basket Reminder 2'],
                    'basket-reminder3'           => $auroraOutboxes['Basket Reminder 3'],
                    'delivery-confirmation'      => $auroraOutboxes['Delivery Confirmation'],
                    'delivery-note-dispatched'   => $auroraOutboxes['Delivery Note Dispatched'],
                    'delivery-note-undispatched' => $auroraOutboxes['Delivery Note Undispatched'],
                    'invite'                     => $auroraOutboxes['Invite'],
                    'invite-full-mailshot'       => $auroraOutboxes['Invite Full Mailshot'],
                    'invite-mailshot'            => $auroraOutboxes['Invite Mailshot'],
                    'invoice-deleted'            => $auroraOutboxes['Invoice Deleted'],
                    'marketing'                  => $auroraOutboxes['Marketing'],
                    'new-order'                  => $auroraOutboxes['New Order'],
                    'newsletter'                 => $auroraOutboxes['Newsletter'],
                    'oos-notification'           => $auroraOutboxes['OOS Notification'],
                    'order-confirmation'         => $auroraOutboxes['Order Confirmation'],
                    'password-reminder'          => $auroraOutboxes['Password Reminder'],
                    'registration'               => $auroraOutboxes['Registration'],
                    'registration-approved'      => $auroraOutboxes['Registration Approved'],
                    'registration-rejected'      => $auroraOutboxes['Registration Rejected'],
                    'reorder-reminder'           => $auroraOutboxes['GR Reminder'],


                    default => null
                };

                if ($sourceId) {
                    $outbox->update(
                        [
                            'source_id' => $organisationSource->getOrganisation()->id.':'.$sourceId
                        ]
                    );
                }



                $sourceData  = explode(':', $shop->source_id);
                $accountData = DB::connection('aurora')->table('Payment Account Dimension')
                    ->select('Payment Account Key')
                    ->leftJoin('Payment Account Store Bridge', 'Payment Account Store Payment Account Key', 'Payment Account Key')
                    ->where('Payment Account Block', 'Accounts')
                    ->where('Payment Account Store Store Key', $sourceData[1])
                    ->first();
                if ($accountData) {

                    $shop->accounts()->update(
                        [
                            'source_id' => $organisationSource->getOrganisation()->id.':'.$accountData->{'Payment Account Key'}
                        ]
                    );
                }

            }


            return $shop;
        }

        return null;
    }


    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Store Dimension')
            ->select('Store Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Store Dimension')->count();
    }
}

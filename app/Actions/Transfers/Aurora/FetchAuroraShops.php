<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 01:27:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Helpers\TaxNumber\DeleteTaxNumber;
use App\Actions\Helpers\TaxNumber\StoreTaxNumber;
use App\Actions\Helpers\TaxNumber\UpdateTaxNumber;
use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Catalogue\Shop;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class FetchAuroraShops extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:shops {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Shop
    {
        if ($shopData = $organisationSource->fetchShop($organisationSourceId)) {
            setPermissionsTeamId($organisationSource->getOrganisation()->group_id);


            if ($shop = Shop::where('source_id', $shopData['shop']['source_id'])
                ->first()) {
                try {
                    $shop = UpdateShop::make()->action(
                        shop: $shop,
                        modelData: $shopData['shop'],
                        hydratorsDelay: $this->hydratorsDelay,
                        strict: false,
                        audit: false
                    );
                } catch (Exception|Throwable $e) {
                    $this->recordError($organisationSource, $e, $shopData['shop'], 'Shop', 'update');

                    return null;
                }

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
            } else {
                try {
                    $shop = StoreShop::make()->action(
                        organisation: $organisationSource->getOrganisation(),
                        modelData: $shopData['shop'],
                        hydratorsDelay: $this->hydratorsDelay,
                        strict: false,
                        audit: false
                    );

                    Shop::enableAuditing();

                    $this->saveMigrationHistory(
                        $shop,
                        Arr::except($shopData['shop'], ['fetched_at', 'last_fetched_at', 'source_id'])
                    );

                    $this->recordNew($organisationSource);

                    $sourceData = explode(':', $shop->source_id);
                    DB::connection('aurora')->table('Store Dimension')
                        ->where('Store Key', $sourceData[1])
                        ->update(['aiku_id' => $shop->id]);
                } catch (Exception|Throwable $e) {
                    $this->recordError($organisationSource, $e, $shopData['shop'], 'Shop', 'store');

                    return null;
                }

                if ($shopData['tax_number']) {
                    StoreTaxNumber::run(
                        owner: $shop,
                        modelData: $shopData['tax_number']
                    );
                }
            }


            $shopSourceId = explode(':', $shop->source_id);

            $auroraOutboxes = DB::connection('aurora')->table('Email Campaign Type Dimension')
                ->where('Email Campaign Type Store Key', $shopSourceId[1])
                ->get()
                ->pluck('Email Campaign Type Key', 'Email Campaign Type Code')->all();


            foreach ($shop->outboxes as $outbox) {
                $sourceId = match ($outbox->type) {
                    OutboxTypeEnum::NEW_CUSTOMER => $auroraOutboxes['New Customer'],
                    OutboxTypeEnum::ABANDONED_CART => $auroraOutboxes['AbandonedCart'],
                    OutboxTypeEnum::BASKET_LOW_STOCK => $auroraOutboxes['Basket Low Stock'] ?? null,
                    OutboxTypeEnum::BASKET_REMINDER_1 => $auroraOutboxes['Basket Reminder 1'],
                    OutboxTypeEnum::BASKET_REMINDER_2 => $auroraOutboxes['Basket Reminder 2'],
                    OutboxTypeEnum::BASKET_REMINDER_3 => $auroraOutboxes['Basket Reminder 3'],
                    OutboxTypeEnum::DELIVERY_CONFIRMATION => $auroraOutboxes['Delivery Confirmation'],
                    OutboxTypeEnum::DELIVERY_NOTE_DISPATCHED => $auroraOutboxes['Delivery Note Dispatched'],
                    OutboxTypeEnum::DELIVERY_NOTE_UNDISPATCHED => $auroraOutboxes['Delivery Note Undispatched'],
                    //    'invite' => $auroraOutboxes['Invite'],
                    //    'invite-full-mailshot' => $auroraOutboxes['Invite Full Mailshot'],
                    //   'invite-mailshot' => $auroraOutboxes['Invite Mailshot'],
                    //   'invoice-deleted' => $auroraOutboxes['Invoice Deleted'],
                    OutboxTypeEnum::MARKETING => $auroraOutboxes['Marketing'],
                    OutboxTypeEnum::NEW_ORDER => $auroraOutboxes['New Order'],
                    OutboxTypeEnum::NEWSLETTER => $auroraOutboxes['Newsletter'],
                    OutboxTypeEnum::OOS_NOTIFICATION => $auroraOutboxes['OOS Notification'],
                    OutboxTypeEnum::ORDER_CONFIRMATION => $auroraOutboxes['Order Confirmation'],
                    OutboxTypeEnum::PASSWORD_REMINDER => $auroraOutboxes['Password Reminder'],
                    OutboxTypeEnum::REGISTRATION => $auroraOutboxes['Registration'],
                    OutboxTypeEnum::REGISTRATION_APPROVED => $auroraOutboxes['Registration Approved'],
                    OutboxTypeEnum::REGISTRATION_REJECTED => $auroraOutboxes['Registration Rejected'],
                    OutboxTypeEnum::REORDER_REMINDER => $auroraOutboxes['GR Reminder'],


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
                    $accounts = $shop->getAccounts();
                    $accounts->update(
                        [
                            'source_id' => $organisationSource->getOrganisation()->id.':'.$accountData->{'Payment Account Key'}
                        ]
                    );

                    if ($accounts->fetched_at) {
                        $accounts->update(
                            [
                                'last_fetched_at' => now()
                            ]
                        );
                    } else {
                        $accounts->update(
                            [
                                'fetched_at' => now()
                            ]
                        );
                    }
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

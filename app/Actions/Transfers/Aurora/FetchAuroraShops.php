<?php

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 01:27:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Accounting\PaymentAccountShop\StorePaymentAccountShop;
use App\Actions\Accounting\PaymentAccountShop\UpdatePaymentAccountShop;
use App\Actions\Helpers\TaxNumber\DeleteTaxNumber;
use App\Actions\Helpers\TaxNumber\StoreTaxNumber;
use App\Actions\Helpers\TaxNumber\UpdateTaxNumber;
use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Enums\Accounting\PaymentAccountShop\PaymentAccountShopStateEnum;
use App\Models\Accounting\PaymentAccountShop;
use App\Models\Catalogue\Shop;
use App\Transfers\Aurora\WithAuroraParsers;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class FetchAuroraShops extends FetchAuroraAction
{
    use WithShopSetOutboxesSourceId;
    use WithAuroraParsers;

    public string $commandSignature = 'fetch:shops {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Shop
    {
        $shopData = $organisationSource->fetchShop($organisationSourceId);
        if ($shopData) {
            setPermissionsTeamId($organisationSource->getOrganisation()->group_id);


            if ($shop = Shop::where('source_id', $shopData['shop']['source_id'])->first()) {
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
                        hydratorsDelay: 5,
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



            $this->setShopSetOutboxesSourceId($shop);

            $sourceData = explode(':', $shop->source_id);


            foreach (
                DB::connection('aurora')->table('Payment Account Dimension')
                    ->leftJoin('Payment Account Store Bridge', 'Payment Account Store Payment Account Key', 'Payment Account Key')
                    ->where('Payment Account Store Store Key', $sourceData[1])
                    ->get() as $paymentAccountData
            ) {
                if ($paymentAccountData->{'Payment Account Block'} == 'Accounts') {
                    $this->setShopPaymentAccountTypeAccount($shop, $paymentAccountData);
                } else {
                    $this->fetchPaymentAccountShop($shop, $paymentAccountData);
                }
            }


            return $shop;
        }

        return null;
    }


    public function fetchPaymentAccountShop(Shop $shop, $accountData): void
    {


        $paymentAccount = $this->parsePaymentAccount($shop->organisation->id.':'.$accountData->{'Payment Account Key'});
        if (!$paymentAccount) {
            exit('Error payment account not found in fetchPaymentAccountShop');
        }

        $paymentAccountShop = PaymentAccountShop::where('shop_id', $shop->id)
            ->where('source_id', $shop->organisation->id.':'.$accountData->{'Payment Account Store Key'})
            ->first();


        $state = match ($accountData->{'Payment Account Store Status'}) {
            'Active' => PaymentAccountShopStateEnum::ACTIVE,
            'Inactive' => PaymentAccountShopStateEnum::INACTIVE,
        };

        $paymentAccountShopData = [
            'show_in_checkout'          => $accountData->{'Payment Account Store Show In Cart'} == 'Yes',
            'checkout_display_position' => $accountData->{'Payment Account Store Show Cart Order'},
            'state'                     => $state,
            'source_id'                 => $shop->organisation->id.':'.$accountData->{'Payment Account Store Key'},
        ];

        $from = $this->parseDatetime($accountData->{'Payment Account Store Valid From'});
        if (!$from) {
            $from = $paymentAccount->created_at;
        }

        if ($from) {
            $paymentAccountShopData['activated_at']      = $from;
            $paymentAccountShopData['last_activated_at'] = $from;
        }


        $data = [];
        if ($accountData->login or $accountData->password or $accountData->public_key or $accountData->hide) {
            $rawSettings = [
                'login'      => $accountData->login,
                'password'   => $accountData->password,
                'public_key' => $accountData->public_key,
                'hide'       => $accountData->hide
            ];


            if ($accountData->{'Payment Account Block'} == 'BTree' and $accountData->hide == 'yes') {
                $data['btree_credit_card_hide'] = true;
            } elseif ($accountData->{'Payment Account Block'} == 'Checkout') {

                if ($accountData->login and $accountData->password and !$accountData->public_key) {
                    $data['legacy'] = true;
                    $data['credentials'] = [
                        'public_key' => $accountData->login,
                        'secret_key' => $accountData->password
                    ];
                } else {
                    $data['credentials'] = [
                        'payment_channel' => $accountData->login,
                    ];
                }
            } elseif ($accountData->{'Payment Account Block'} == 'Hokodo' and $accountData->login and !$accountData->password and $accountData->public_key) {
                $data['credentials'] = [
                    'public_key' => $accountData->public_key,
                    'id' => $accountData->login
                ];
            } else {
                dd($accountData, $rawSettings);
            }
        }

        $paymentAccountShopData['data'] = $data;

        if ($paymentAccountShop) {
            $paymentAccountShopData['data'] = array_merge($paymentAccountShop->data, $data);

            UpdatePaymentAccountShop::make()->action(
                paymentAccountShop: $paymentAccountShop,
                modelData: $paymentAccountShopData,
                strict: false,
                audit: false
            );
        } else {
            //print_r($accountData);

            StorePaymentAccountShop::make()->action(
                paymentAccount: $paymentAccount,
                shop: $shop,
                modelData: $paymentAccountShopData,
                strict: false,
                audit: false
            );
            // dd($accountData,$paymentAccountShopData);

        }
    }

    public function setShopPaymentAccountTypeAccount(Shop $shop, $accountData): void
    {
        $accounts = $shop->getPaymentAccountTypeAccount();

        if (!$accounts) {
            exit('Error shop payment account type account not found');
        }
        $accounts->update(
            [
                'source_id' => $shop->organisation->id.':'.$accountData->{'Payment Account Key'}
            ]
        );

        $paymentAccountShop = PaymentAccountShop::where('shop_id', $shop->id)
            ->where('payment_account_id', $accounts->id)
            ->first();

        $paymentAccountShop->update(
            [
                'source_id' => $shop->organisation->id.':'.$accountData->{'Payment Account Store Key'}
            ]
        );

        if ($accounts->fetched_at) {
            $accounts->update(
                [
                    'last_fetched_at' => now(),
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

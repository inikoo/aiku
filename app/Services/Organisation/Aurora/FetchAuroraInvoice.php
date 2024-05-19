<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 19:15:57 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Actions\Helpers\CurrencyExchange\GetHistoricCurrencyExchange;
use App\Models\Helpers\Address;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FetchAuroraInvoice extends FetchAurora
{
    protected function parseModel(): void
    {
        if (!$this->auroraModelData->{'Invoice Order Key'} and $this->auroraModelData->{'Invoice Total Amount'} == 0) {
            // just ignore it
            return;
        }

        if (!$this->auroraModelData->{'Invoice Order Key'}) {
            // just ignore as well
            return;
        }


        $order = $this->parseOrder($this->organisation->id.':'.$this->auroraModelData->{'Invoice Order Key'});
        if (!$order) {
            $this->parsedData['parent'] = $this->parseCustomer($this->organisation->id.':'.$this->auroraModelData->{'Invoice Customer Key'});
        } else {
            $this->parsedData['parent'] = $order;
        }


        $data = [];

        $data['foot_note'] = $this->auroraModelData->{'Invoice Message'};

        $billingAddressData = $this->parseAddress(prefix: 'Invoice', auAddressData: $this->auroraModelData);

        $date = $this->parseDate($this->auroraModelData->{'Invoice Date'});
        $date = new Carbon($date);

        $taxLiabilityAt = $this->parseDate($this->auroraModelData->{'Invoice Tax Liability Date'});
        if (!$taxLiabilityAt) {
            $taxLiabilityAt = $this->auroraModelData->{'Invoice Date'};
        }
        $this->parsedData['invoice'] = [
            'number'                  => $this->auroraModelData->{'Invoice Public ID'},
            'type'                    => strtolower($this->auroraModelData->{'Invoice Type'}),
            'created_at'              => $this->auroraModelData->{'Invoice Date'},
            'date'                    => $this->auroraModelData->{'Invoice Date'},
            'tax_liability_at'        => $taxLiabilityAt,
            'exchange'                => $this->auroraModelData->{'Invoice Currency Exchange'},
            'net_amount'              => $this->auroraModelData->{'Invoice Total Net Amount'},
            'total_amount'            => $this->auroraModelData->{'Invoice Total Amount'},

            'org_exchange'   => GetHistoricCurrencyExchange::run($this->parsedData['parent']->shop->currency, $this->parsedData['parent']->organisation->currency, $date),
            'group_exchange' => GetHistoricCurrencyExchange::run($this->parsedData['parent']->shop->currency, $this->parsedData['parent']->group->currency, $date),


            'org_net_amount'   => $this->auroraModelData->{'Invoice Total Net Amount'} * GetHistoricCurrencyExchange::run($this->parsedData['parent']->shop->currency, $this->parsedData['parent']->organisation->currency, $date),
            'group_net_amount' => $this->auroraModelData->{'Invoice Total Net Amount'} * GetHistoricCurrencyExchange::run($this->parsedData['parent']->shop->currency, $this->parsedData['parent']->group->currency, $date),

            'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'Invoice Key'},
            'data'            => $data,
            'billing_address' => new Address($billingAddressData),
            'currency_id'     => $this->parseCurrencyID($this->auroraModelData->{'Invoice Currency'}),

        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Invoice Dimension')
            ->where('Invoice Key', $id)->first();
    }
}

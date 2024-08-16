<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\CRM\Customer\CustomerStateEnum;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraCustomer extends FetchAurora
{
    protected function parseModel(): void
    {
        $this->parsedData['shop'] = $this->parseShop($this->organisation->id.':'.$this->auroraModelData->{'Customer Store Key'});

        $status = CustomerStatusEnum::APPROVED->value;
        $state  = CustomerStateEnum::ACTIVE->value;
        if ($this->auroraModelData->{'Customer Type by Activity'} == 'Rejected') {
            $status = CustomerStatusEnum::REJECTED->value;
        } elseif ($this->auroraModelData->{'Customer Type by Activity'} == 'ToApprove') {
            $state  = CustomerStateEnum::REGISTERED->value;
            $status = CustomerStatusEnum::PENDING_APPROVAL->value;
        } elseif ($this->auroraModelData->{'Customer Type by Activity'} == 'Losing') {
            $state = CustomerStateEnum::LOSING->value;
        } elseif ($this->auroraModelData->{'Customer Type by Activity'} == 'Lost') {
            $state = CustomerStateEnum::LOST->value;
        }

        $billingAddress  = $this->parseAddress(prefix: 'Customer Invoice', auAddressData: $this->auroraModelData);
        $deliveryAddress = $this->parseAddress(prefix: 'Customer Delivery', auAddressData: $this->auroraModelData);

        if (Arr::get($billingAddress, 'country_id') == null) {
            $billingAddress['country_id'] = $this->parsedData['shop']->country_id;
        }
        if (Arr::get($deliveryAddress, 'country_id') == null) {
            $deliveryAddress['country_id'] = $this->parsedData['shop']->country_id;
        }

        $taxNumber = $this->parseTaxNumber(
            number: $this->auroraModelData->{'Customer Tax Number'},
            countryID: $billingAddress['country_id'],
            rawData: (array)$this->auroraModelData
        );


        $contactName = $this->auroraModelData->{'Customer Main Contact Name'};
        $company     = $this->auroraModelData->{'Customer Company Name'};


        $contactName = $this->cleanName($contactName);

        $company = $this->cleanName($company);
        $company = $this->cleanCompanyName($company);


        if (!$company and !$contactName) {
            $contactName = $this->auroraModelData->{'Customer Name'};
            $contactName = $this->cleanName($contactName);
            $contactName = $this->cleanCompanyName($contactName);
        }

        $phone = $this->cleanPhone($this->auroraModelData->{'Customer Main Plain Mobile'});
        if ($phone == '') {
            $phone = $this->cleanPhone($this->auroraModelData->{'Customer Main Plain Telephone'});
        }

        if (is_numeric($contactName)) {
            $tmp = preg_replace('/[^0-9]/i', '', $contactName);
            $tmp = (string)preg_replace('/^0/', '', $tmp);

            if (strlen($contactName) > 6 and preg_match("/$tmp/", $phone)) {
                $contactName = '';
            }
            if ($contactName != '' and $company == '') {
                $company     = $contactName;
                $contactName = '';
            }
        }

        $this->parsedData['customer'] =
            [
                'reference'       => sprintf('%05d', $this->auroraModelData->{'Customer Key'}),
                'state'           => $state,
                'status'          => $status,
                'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'Customer Key'},
                'created_at'      => $this->auroraModelData->{'Customer First Contacted Date'},
                'contact_address' => $billingAddress,
                'tax_number'      => $taxNumber,
                'fetched_at'      => now(),
                'last_fetched_at' => now()
            ];

        if ($contactName != '') {
            $this->parsedData['customer']['contact_name'] = $contactName;
        }

        if ($company != '') {
            $this->parsedData['customer']['company_name'] = $company;
        }

        $email = $this->auroraModelData->{'Customer Main Plain Email'};
        if ($email != '') {
            $this->parsedData['customer']['email'] = $email;
        }

        if ($phone != '') {
            $this->parsedData['customer']['phone'] = $phone;
        }

        $website = $this->cleanUrl($this->auroraModelData->{'Customer Website'});
        if ($website != '') {
            $this->parsedData['customer']['contact_website'] = $website;
        }

        $identityDocumentNumber = $this->cleanCompanyNumber(Str::limit($this->auroraModelData->{'Customer Registration Number'}));
        if ($identityDocumentNumber != '' and $company != $identityDocumentNumber) {
            $this->parsedData['customer']['identity_document_number'] = $identityDocumentNumber;
        }


        //print_r($this->parsedData['customer']);

        if ($billingAddress != $deliveryAddress) {
            $this->parsedData['customer']['delivery_address'] = $deliveryAddress;
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Customer Dimension')
            ->where('Customer Key', $id)->first();
    }


    protected function cleanCompanyName($name): string
    {
        $name = str_replace('(Ebay/Amazon Account)', '', $name);
        $name = trim($name);

        if (preg_match('/-*\s*(None|Not yet started trading|undecided as of yet)\s*-*/', $name)) {
            $name = '';
        }

        if (in_array($name, ['none', '0n-line Amazon retail', '-', 'Unknown', '- None -', '- Not yet started trading -', '- Select -', '--Please Select--'])) {
            $name = '';
        }

        return $name;
    }

    protected function cleanName($name): string
    {
        $name = trim($name);

        if (preg_match('/^([:\-.+,*01?#_\/])+$/', $name)) {
            $name = '';
        }

        return $name;
    }


    protected function cleanCompanyNumber($string): string
    {
        $string = $this->cleanName($string);
        $string = $this->cleanCompanyName($string);

        $string = preg_replace('/^([:;])\s*/', '', $string);


        if (preg_match('/(No tengo|Don.?t have|Dont have|I don.t have|not Applicable|Not available|Not yet|Sheffield|independente em nome próprio|Sole Trader|carmen|carlos|En proceso|entrepreneur|unknown|test|to follow|Under construction)/i', $string)) {
            $string = '';
        }

        if (in_array($string, [
            'Zürich',
            'universes',
            'sra.',
            'Noch nicht vorhanden',
            'No hay número de registro',
            'Slovenská Republika',
            'slovensko',
            'Slovensko/Slovakia',
            'recargo de equivalencia',
            'nose',
            'No Selection',
            'No lo se',
            'Empresa en nombre individual',
            'new business',
            'En projet',
            '99999999999999999999999999999',
            'Bratislavský',
            'Česká Republika',
            'Miss',
            '65 avenue de la Chaumière',
            'autonomo',
            'Autónoma',
            'Autonomo',
            'Aún no tengo',
            'Slovensko',
            'autoentrepreneur',
            'Slovensko (SK)',
            'Wien',
            'N /a',
            'N.A.',
            'N/A Local Authority',
            'n/a partnershiop',
            'Na sole trader',
            'Não tenho',
            'Česká republika',
            'Sra.',
            'Sra',
            'Sra.',
            'Sra',
            'Sra.',
            'Sra',
            'Sra.',
            'Sra',
            '///',
            '1100 Tatra banka, a.s.',
            '21% IVA (NO RECARGO)',
            '5,2%',
            '5,20',
            'Barcelona',
            "Ali'ne baba et ses 4500 produits",
            'SK',
            'GB',
            'EL',
            'na',
            'NA',
            'n/a',
            'N/A',
            'N/a',
            'en cour',
            'en cours',
            'MT',
            '-SR',
            '000 000 000 000 00',
            'Slovenská republika',
            'En cours pas recu encore',
            'Slowakei',
            'Slovakia'
        ])) {
            $string = '';
        }

        if (strlen($string) < 4) {
            $string = '';
        }

        return $string;
    }


    protected function cleanUrl($url): string
    {
        $url = $this->cleanName($url);
        if (in_array($url, ['www.'])) {
            $url = '';
        }

        return $url;
    }


    protected function cleanPhone($phone): string
    {
        $phone = $this->cleanName($phone);
        if (in_array($phone, [
            ')575€43347',
            '+xxx',
            'na',
            'n/a',
            'N/A',
            'N/a',
            '=!/&&$!%"§(=',
            'NotAccessible',
            'why',
            'Y',
            '\\',
            'Xxxxxx.',
            'Om'

        ])) {
            $phone = '';
        }

        $digitsOnly = preg_replace('/[^0-9]/i', '', $phone);
        if (strlen($digitsOnly) < 5) {
            $phone = '';
        }

        return $phone;
    }

}

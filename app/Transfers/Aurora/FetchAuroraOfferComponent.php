<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 10 Nov 2024 12:20:49 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Actions\Utils\Abbreviate;
use App\Enums\Discounts\OfferComponent\OfferComponentStateEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraOfferComponent extends FetchAurora
{
    protected function parseModel(): void
    {
        $offer = $this->parseOffer($this->organisation->id.':'.$this->auroraModelData->{'Deal Component Deal Key'});

        if (!$offer) {
            return;
        }
        //enum('Category','Department','Family','Product','Order','Customer','Customer Category','Customer List')
        if (in_array($this->auroraModelData->{'Deal Component Trigger'}, ['Product', 'Department', 'Family', 'Category', 'Customer']) and
            !$this->auroraModelData->{'Deal Component Trigger Key'}
        ) {
            return;
        }


        $state = match ($this->auroraModelData->{'Deal Component Status'}) {
            'Waiting' => OfferComponentStateEnum::IN_PROCESS,
            'Finish' => OfferComponentStateEnum::FINISHED,
            default => OfferComponentStateEnum::ACTIVE
        };


        $type = $this->auroraModelData->{'Deal Component Terms Type'};

        if ($type == '') {
            return;
        }

        $trigger      = null;
        $trigger_type = null;


        switch ($this->auroraModelData->{'Deal Component Trigger'}) {
            case 'Product':
                $trigger = $this->parseProduct($this->organisation->id.':'.$this->auroraModelData->{'Deal Component Trigger Key'});
                break;
            case 'Department':
                $trigger = $this->parseDepartment($this->organisation->id.':'.$this->auroraModelData->{'Deal Component Trigger Key'});
                break;
            case 'Family':
            case 'Category':
                $trigger = $this->parseFamily($this->organisation->id.':'.$this->auroraModelData->{'Deal Component Trigger Key'});

                break;
            case 'Customer':
                $trigger = $this->parseCustomer($this->organisation->id.':'.$this->auroraModelData->{'Deal Component Trigger Key'});
                break;
            case 'Order':
                $trigger = $offer->shop;
                break;
            case 'Customer List':
                return;
            default:
                dd($this->auroraModelData);
        }

        if ($trigger_type == null and $trigger == null) {
            return;
        }


        $code = Abbreviate::run($this->auroraModelData->{'Deal Component Trigger'});
        if ($this->auroraModelData->{'Deal Component Terms Type'} != null) {
            $code .= Abbreviate::run($this->auroraModelData->{'Deal Component Terms Type'});
        }
        if ($this->auroraModelData->{'Deal Component Allowance Type'} != null) {
            $code .= Abbreviate::run($this->auroraModelData->{'Deal Component Allowance Type'});
        }
        if ($this->auroraModelData->{'Deal Component Allowance Target'} != null) {
            $code .= Abbreviate::run($this->auroraModelData->{'Deal Component Allowance Target'});
        }


        $code = $this->cleanOfferCode($code);
        $code = strtolower($code);

        if ($code != '') {
            $code = $code.'-';
        }
        $code = $code.$this->auroraModelData->{'Deal Component Key'};


        $this->parsedData['trigger']        = $trigger;
        $this->parsedData['offer']          = $offer;
        $this->parsedData['offerComponent'] = [
            'code'            => $code,
            'state'           => $state,
            'type'            => $type,
            'trigger_scope'   => $this->auroraModelData->{'Deal Component Trigger Scope Type'},
            'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'Deal Component Key'},
            'fetched_at'      => now(),
            'last_fetched_at' => now(),
            'start_at'        => $this->parseDatetime($this->auroraModelData->{'Deal Component Begin Date'}),
        ];

        if ($endAt = $this->parseDatetime($this->auroraModelData->{'Deal Component Expiration Date'})) {
            $this->parsedData['offer']['end_at'] = $endAt;
        }

        if ($trigger_type) {
            $this->parsedData['offer']['trigger_type'] = $trigger_type;
        }

        $createdBy = $this->auroraModelData->{'Deal Component Begin Date'};

        if ($createdBy) {
            $this->parsedData['offer']['created_by'] = $createdBy;
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Deal Component Dimension')
            ->where('Deal Component Key', $id)->first();
    }
}
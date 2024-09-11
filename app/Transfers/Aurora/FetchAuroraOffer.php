<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 11 Sept 2024 19:51:35 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Actions\Utils\Abbreviate;
use App\Enums\Discounts\Offer\OfferStateEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraOffer extends FetchAurora
{
    protected function parseModel(): void
    {
        $offerCampaign = $this->parseOfferCampaign($this->organisation->id.':'.$this->auroraModelData->{'Deal Campaign Key'});

        if (!$offerCampaign) {
            return;
        }

        if (in_array($this->auroraModelData->{'Deal Trigger'}, ['Product', 'Department', 'Family', 'Category', 'Customer']) and
            !$this->auroraModelData->{'Deal Trigger Key'}
        ) {
            return;
        }

        $status = false;
        $state  = match ($this->auroraModelData->{'Deal Status'}) {
            'Waiting' => OfferStateEnum::IN_PROCESS,
            'Finish'  => OfferStateEnum::FINISHED,
            default   => OfferStateEnum::ACTIVE
        };

        if ($this->auroraModelData->{'Deal Status'} == 'Active') {
            $status = true;
        }
        $type = $this->auroraModelData->{'Deal Terms Type'};

        if($type=='') {
            return;
        }

        $trigger      = null;
        $trigger_type = null;


        switch ($this->auroraModelData->{'Deal Trigger'}) {
            case 'Product':
                $trigger = $this->parseProduct($this->organisation->id.':'.$this->auroraModelData->{'Deal Trigger Key'});
                break;
            case 'Department':
                $trigger = $this->parseDepartment($this->organisation->id.':'.$this->auroraModelData->{'Deal Trigger Key'});
                break;
            case 'Family':
            case 'Category':
                $trigger = $this->parseFamily($this->organisation->id.':'.$this->auroraModelData->{'Deal Trigger Key'});

                break;
            case 'Customer':
                $trigger = $this->parseCustomer($this->organisation->id.':'.$this->auroraModelData->{'Deal Trigger Key'});
                break;
            case 'Order':
                $trigger_type = 'Order';
                break;
            case 'Customer List':
                return;
            default:
                dd($this->auroraModelData);
        }

        if ($trigger_type == null and $trigger == null) {
            return;
        }

        $name = $this->auroraModelData->{'Deal Name'};
        if (!$name) {
            $name = $this->auroraModelData->{'Deal Name Label'};
        }



        $code = preg_replace('/-/', '', $name);
        $code = preg_replace('/%/', 'off', $code);
        $code = preg_replace('/\s|@|\$|§|}|€|!/', '', $code);
        $code = preg_replace('/3\/2/', '3x2', $code);
        $code = preg_replace('/\//', '-', $code);
        $code = preg_replace('/\+/', 'plus', $code);



        $code = Abbreviate::run($code, maximumLength: 32);
        $code =strtolower($code);

        if($code!='') {
            $code=$code.'-';
        }
        $code=$code.$this->auroraModelData->{'Deal Key'};

        if($name=='') {
            $name=$code;
        }


        $this->parsedData['trigger']        = $trigger;
        $this->parsedData['offer_campaign'] = $offerCampaign;
        $this->parsedData['offer']          = [
            'code'            => $code,
            'name'            => $name,
            'status'          => $status,
            'state'           => $state,
            'type'            => $type,
            'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'Deal Key'},
            'fetched_at'      => now(),
            'last_fetched_at' => now(),
            'start_at'        => $this->parseDatetime($this->auroraModelData->{'Deal Begin Date'}),
        ];

        if ($endAt = $this->parseDatetime($this->auroraModelData->{'Deal Expiration Date'})) {
            $this->parsedData['offer']['end_at'] = $endAt;
        }

        if ($trigger_type) {
            $this->parsedData['offer']['trigger_type'] = $trigger_type;
        }

        $createdBy = $this->auroraModelData->{'Deal Begin Date'};

        if ($createdBy) {
            $this->parsedData['offer']['created_by'] = $createdBy;
        }


    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Deal Dimension')
            ->where('Deal Key', $id)->first();
    }
}

<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 07-02-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\OrgAction;
use App\Actions\Traits\Hydrators\WithHydrateCommand;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Models\Fulfilment\PalletDelivery;

class SetPalletDeliveryDate extends OrgAction
{
    use WithHydrateCommand;

    public string $commandSignature = 'pallet_delivery:set_pallet_delivery_date {organisations?*} {--S|shop= shop slug} {--s|slug=}';


    public function __construct()
    {
        $this->model = PalletDelivery::class;
    }

    protected function handle(PalletDelivery $palletDelivery): PalletDelivery
    {

        $date = null;
        switch ($palletDelivery->state) {
            case PalletDeliveryStateEnum::IN_PROCESS:
                if ($palletDelivery->estimated_delivery_date) {
                    $date = $palletDelivery->estimated_delivery_date;
                } else {
                    $date = $palletDelivery->in_process_at;
                }
                break;
            case PalletDeliveryStateEnum::SUBMITTED:
                $date = $palletDelivery->submitted_at;
                break;
            case PalletDeliveryStateEnum::CONFIRMED:
                $date = $palletDelivery->confirmed_at;
                break;
            case PalletDeliveryStateEnum::RECEIVED:
                $date = $palletDelivery->received_at;
                break;
            case PalletDeliveryStateEnum::NOT_RECEIVED:
                $date = $palletDelivery->not_received_at;
                break;
            case PalletDeliveryStateEnum::BOOKING_IN:
                $date = $palletDelivery->booking_in_at;
                break;
            case PalletDeliveryStateEnum::BOOKED_IN:
                $date = $palletDelivery->booked_in_at;
                break;
            default:
                break;
        }

        if ($date) {
            $palletDelivery->update(
                [
                    'date' => $date,
                ]
            );
        }

        return $palletDelivery;
    }

}

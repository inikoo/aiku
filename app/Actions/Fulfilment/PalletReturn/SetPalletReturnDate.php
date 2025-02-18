<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 18-02-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\OrgAction;
use App\Actions\Traits\Hydrators\WithHydrateCommand;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Models\Fulfilment\PalletReturn;

class SetPalletReturnDate extends OrgAction
{
    use WithHydrateCommand;

    public string $commandSignature = 'pallet_return:set_pallet_return_date {organisations?*} {--S|shop= shop slug} {--s|slug=}';


    public function __construct()
    {
        $this->model = PalletReturn::class;
    }

    protected function handle(PalletReturn $palletReturn): PalletReturn
    {

        $date = null;
        switch ($palletReturn->state) {
            case PalletReturnStateEnum::IN_PROCESS:
                $date = $palletReturn->in_process_at;
                break;
            case PalletReturnStateEnum::SUBMITTED:
                $date = $palletReturn->submitted_at;
                break;
            case PalletReturnStateEnum::CONFIRMED:
                $date = $palletReturn->confirmed_at;
                break;
            case PalletReturnStateEnum::PICKED:
                $date = $palletReturn->picked_at;
                break;
            case PalletReturnStateEnum::PICKING:
                $date = $palletReturn->picking_at;
                break;
            case PalletReturnStateEnum::DISPATCHED:
                $date = $palletReturn->dispatched_at;
                break;
            case PalletReturnStateEnum::CANCEL:
                $date = $palletReturn->cancel_at;
                break;
            default:
                break;
        }

        if ($date) {
            $palletReturn->update(
                [
                    'date' => $date,
                ]
            );
        }

        return $palletReturn;
    }



}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Mar 2024 14:25:17 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Models\Fulfilment\PalletDelivery;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdatePalletDeliveryStateFromItems
{
    use WithActionUpdate;
    use AsAction;

    public function handle(PalletDelivery $palletDelivery): PalletDelivery
    {
        $palletCount                 = $palletDelivery->pallets()->count();
        $palletStateBookedInCount    = $palletDelivery->pallets()->where('state', PalletStateEnum::BOOKED_IN)->count();
        $palletStateNotReceivedCount = $palletDelivery->pallets()->where('state', PalletStateEnum::NOT_RECEIVED)->count(
        );
        $palletStateReceivedCount = $palletDelivery->pallets()->where('state', PalletStateEnum::RECEIVED)->count();

        $palletReceivedCount = $palletStateReceivedCount + $palletStateNotReceivedCount + $palletStateBookedInCount;

        $palletNotInRentalCount = $palletDelivery->pallets()->whereNull('rental_id')->count();

        //print "pallets $palletCount received $palletReceivedCount $palletStateBookedInCount $palletStateNotReceivedCount $palletStateReceivedCount\n";


        if (in_array($palletDelivery->state->value, [
            PalletDeliveryStateEnum::RECEIVED->value,
            PalletDeliveryStateEnum::CONFIRMED->value,
            PalletDeliveryStateEnum::NOT_RECEIVED->value,

        ])) {
            if ($palletReceivedCount == 0 and $palletNotInRentalCount == 0) {
                return $palletDelivery;
            }

            if ($palletReceivedCount == $palletStateNotReceivedCount and $palletNotInRentalCount > 0) {
                return NotReceivedPalletDelivery::run($palletDelivery);
            }

            return $this->update(
                $palletDelivery,
                ['state'            => PalletDeliveryStateEnum::BOOKING_IN,
                    'booking_in_at' => now()]
            );
        }


        return $palletDelivery;
    }

    public string $commandSignature = 'pallet-delivery:update-state {slug}';

    public function asCommand(Command $command): int
    {
        $exitCode = 0;

        try {
            $palletDelivery = PalletDelivery::where('slug', $command->argument('slug'))->firstOrFail();
        } catch (Exception) {
            $command->error('Pallet Delivery not found');
            return 1;
        }



        $palletDelivery=$this->handle($palletDelivery);

        $command->info("Pallet Delivery $palletDelivery->reference has state ".$palletDelivery->state->value." 🎉");
        return $exitCode;
    }


}

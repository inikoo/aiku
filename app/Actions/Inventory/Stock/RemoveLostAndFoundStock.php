<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 15 May 2023 16:45:46 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Stock;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Inventory\LostAndFoundStock;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveLostAndFoundStock
{
    use AsAction;
    use WithActionUpdate;

    public function handle(LostAndFoundStock $lostAndFoundStock, float $quantity): LostAndFoundStock
    {
        $this->update($lostAndFoundStock, [
            'quantity' => $lostAndFoundStock->quantity - $quantity
        ]);

        if($lostAndFoundStock->quantity <= 0) {
            $lostAndFoundStock->delete();
        }

        return $lostAndFoundStock;
    }

    public function action(LostAndFoundStock $lostAndFoundStock, float $quantity): LostAndFoundStock
    {
        return $this->handle($lostAndFoundStock, $quantity);
    }
}

<?php
/*
 * author Arya Permana - Kirin
 * created on 04-11-2024-15h-25m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Ordering\Purge\UI;

use App\Models\Ordering\Purge;
use Lorisleiva\Actions\Concerns\AsObject;

class GetPurgeShowcase
{
    use AsObject;

    public function handle(Purge $purge): array
    {
        return [
        ];
    }
}

<?php
/*
 * author Arya Permana - Kirin
 * created on 01-11-2024-11h-17m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Enums\Ordering\Purge;

use App\Enums\EnumHelperTrait;

enum PurgedOrderStatusEnum: string
{
    use EnumHelperTrait;
    
    case IN_PROCESS    = 'in-process';
    case PURGED    = 'purged';
    case CANCELLED       = 'cancelled';
    case ERROR       = 'error';
}

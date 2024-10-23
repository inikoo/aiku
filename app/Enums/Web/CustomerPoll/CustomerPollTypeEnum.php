<?php
/*
 * author Arya Permana - Kirin
 * created on 23-10-2024-08h-55m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Enums\Web\CustomerPoll;

use App\Enums\EnumHelperTrait;

enum CustomerPollTypeEnum: string
{
    use EnumHelperTrait;

    case OPEN_QUESTION     = 'open_question';
    case OPTION            = 'option';

}
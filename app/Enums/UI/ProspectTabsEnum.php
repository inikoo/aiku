<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ProspectTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case DATA                               = 'data';
    case COMMUNICATIONS_HISTORY_NOTES       = 'communications_history_notes';
    case SENT_EMAILS                        = 'properties_operations';



    public function blueprint(): array
    {
        return match ($this) {
            ProspectTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            ProspectTabsEnum::COMMUNICATIONS_HISTORY_NOTES => [
                'title' => __('communications, history, notes'),
                'icon'  => 'fal fa-comment',
            ],
            ProspectTabsEnum::SENT_EMAILS => [
                'title' => __('sent emails'),
                'icon'  => 'fal fa-paper-plane',
            ],
        };
    }
}

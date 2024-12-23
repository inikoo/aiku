<?php
/*
 * author Arya Permana - Kirin
 * created on 23-12-2024-16h-12m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\CRM\Customer\UI;

use App\Enums\CRM\Customer\CustomerStateEnum;
use App\Enums\CRM\Prospect\ProspectContactedStateEnum;
use App\Enums\CRM\Prospect\ProspectFailStatusEnum;
use App\Enums\CRM\Prospect\ProspectStateEnum;
use App\Enums\CRM\Prospect\ProspectSuccessStatusEnum;
use App\Models\Catalogue\Shop;
use Lorisleiva\Actions\Concerns\AsObject;

class GetCustomersDashboard
{
    use AsObject;

    public function handle(Shop $parent): array
    {
        $stats = [];


        $stats['customers'] = [
            'label' => __('Customers'),
            'count' => $parent->crmStats->number_customers
        ];
        foreach (CustomerStateEnum::cases() as $case) {
            $stats['customers']['cases'][$case->value] = [
                'value' => $case->value,
                'icon'  => CustomerStateEnum::stateIcon()[$case->value],
                'count' => CustomerStateEnum::count($parent)[$case->value],
                'label' => CustomerStateEnum::labels()[$case->value]
            ];
        }

        return [
            'prospectStats' => $stats,
        ];
    }

}

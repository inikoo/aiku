<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 24 Aug 2022 15:57:30 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Services\Organisation\Aurora;

use App\Models\Assets\Language;
use Carbon\Carbon;
use Exception;

trait WithAuroraParsers
{

    protected function parseDate($value): ?string
    {
        return ($value != '' && $value != '0000-00-00 00:00:00'
            && $value != '2018-00-00 00:00:00') ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    protected function parseLanguageID($locale): int|null
    {
        if ($locale != '') {
            try {
                return Language::where(
                    'code',
                    match ($locale) {
                        'zh_CN.UTF-8' => 'zh-CN',
                        default => substr($locale, 0, 2)
                    }
                )->first()->id;
            } catch (Exception) {
                //print "Locale $locale not found\n";

                return null;
            }
        }

        return null;
    }

    protected function parseJobPosition($isSupervisor, $sourceCode): string
    {
        return match ($sourceCode) {
            'WAHM' => 'wah-m',
            'WAHSK' => 'wah-sk',
            'WAHSC' => 'wah-sc',
            'PICK' => 'dist-pik,dist-pak',
            'OHADM' => 'dist-m',
            'PRODM' => 'prod-m',
            'PRODO' => 'prod-w',
            'CUSM' => 'cus-m',
            'CUS' => 'cus-c',
            'MRK' => $isSupervisor ? 'mrk-m' : 'mrk-c',
            'WEB' => $isSupervisor ? 'web-m' : 'web-c',
            'HR' => $isSupervisor ? 'hr-m' : 'hr-c',
            default => strtolower($sourceCode)
        };
    }
}

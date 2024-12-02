<?php

/*
 * author Arya Permana - Kirin
 * created on 10-10-2024-14h-36m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Traits\WebBlocks;

use Lorisleiva\Actions\Concerns\AsAction;

trait WithFetchScriptWebBlock
{
    use AsAction;
    public function processScriptData($auroraBlock): array
    {
        $rawScript = $auroraBlock['html'] ?? $auroraBlock['src'];
        preg_match('/<script\b[^>]*>(.*?)<\/script>/is', $rawScript, $matches);
        $script = $matches[0] ?? null;
        $cleaned_script = preg_replace('/\s+/', ' ', trim($script));

        data_set($layout, "data.fieldValue.value", $cleaned_script);

        return $layout;
    }
}

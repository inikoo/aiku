<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 28 Apr 2023 12:27:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\SerialReference;

use App\Models\Helpers\SerialReference;
use App\Models\Market\Shop;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\DB;

class GetSerialReference
{
    use AsAction;

    /**
     * @throws \Throwable
     */
    public function handle(Shop $container, $modelType): string
    {
        $serialReference = $this->getSerialReference($container, $modelType);

        $serial=DB::transaction(function () use ($serialReference) {
            $res = DB::connection('group')->table('serial_references')->select('serial')
                ->where('id', $serialReference->id)->first();

            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $serial = (int) $res->serial + 1;


            DB::connection('group')->table('serial_references')
                ->where('id', $serialReference->id)
                ->update(['serial' => $serial]);
            return $serial;
        });

        return sprintf($serialReference->format, $serial);
    }


    private function getSerialReference($container, $modelType): SerialReference
    {
        return match (class_basename($container)) {
            'Shop' => $container->serialReferences()->where('model', $modelType)->firstOrFail()
        };
    }

}

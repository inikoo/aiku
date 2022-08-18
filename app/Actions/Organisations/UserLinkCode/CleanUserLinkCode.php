<?php /** @noinspection PhpUnused */

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 18 Aug 2022 22:51:19 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Organisations\UserLinkCode;



use App\Models\Organisations\UserLinkCode;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class CleanUserLinkCode
{
    use AsAction;

    public string $commandSignature = 'maintenance:user-link-code';


    public function handle(): void
    {
        $date  = Carbon::now()->subHours( 2 );
        UserLinkCode::where( 'updated_at', '<=', $date )->delete();
    }

    public function asCommand(): void
    {
        $this->handle();
    }


}

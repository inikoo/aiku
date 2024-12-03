<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>  
 * Created: Tue, 03 Dec 2024 18:15:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace Tests\Actions\Utils;

use App\Actions\Utils\IsGoogleIp;

test('test if is google ip action works', function () {
    $isGoogleIP=IsGoogleIp::run('10.0.0.0');
    expect($isGoogleIP)->toBeFalse();
    $isGoogleIP=IsGoogleIp::run('64.15.112.1');
    expect($isGoogleIP)->toBeTrue();
});
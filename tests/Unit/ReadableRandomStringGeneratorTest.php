<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>  
 * Created: Tue, 03 Dec 2024 18:13:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace Tests\Actions\Utils;

use App\Actions\Utils\ReadableRandomStringGenerator;

test('test if readable random string generator works', function () {
    $string = ReadableRandomStringGenerator::run();
    expect(strlen($string))->toBe(6);

});
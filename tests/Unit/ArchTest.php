<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 19:36:03 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


test('globals')
    ->expect(['dd', 'dump','print_r'])
    ->not->toBeUsed();

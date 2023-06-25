<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 19:36:03 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */



test('Not debugging statements are left in our code.')
    ->expect(['dd', 'dump','print_r','ray'])
    ->not->toBeUsed();

// Need to create a test that check if Store|Update actions has authorize method

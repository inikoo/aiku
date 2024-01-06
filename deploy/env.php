<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 Jan 2024 14:23:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace Deployer;

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(__DIR__ . '/../')->load();

desc('Inject all necessary .env variables inside deployer config');
task('install:env', function () {

    $environment=currentHost()->get('environment');
    if($environment=='production') {
        set('remote_user', env('DEPLOY_REMOTE_USER'));
    } else {
        set('remote_user', env('DEPLOY_REMOTE_DEVOPS_USER'));
    }
});

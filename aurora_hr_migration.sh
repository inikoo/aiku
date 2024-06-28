#!/bin/bash
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Thu, 13 Jun 2024 06:45:11 Central European Summer Time, Kuala Lumpur, Malaysia
# Copyright (c) 2024, Raul A Perusquia Flores
#


PHP=php
DB_SUFFIX=_base

PHP="${1:-$PHP}"
DB_SUFFIX="${2:-$DB_SUFFIX}"


echo -e "🧼 Migrating human resources models"

${PHP} artisan fetch:clocking-machines -d _base "${DB_SUFFIX}"
${PHP} artisan fetch:employees  -d "${DB_SUFFIX}"
${PHP} artisan fetch:wow-employees
${PHP} artisan fetch:deleted-employees  -d "${DB_SUFFIX}"
${PHP} artisan fetch:timesheets -d "${DB_SUFFIX}"
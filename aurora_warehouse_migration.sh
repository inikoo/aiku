#!/bin/bash
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Thu, 13 Jun 2024 06:40:34 Central European Summer Time, Kuala Lumpur, Malaysia
# Copyright (c) 2024, Raul A Perusquia Flores
#

PHP=php
DB_SUFFIX=

PHP="${1:-$PHP}"
DB_SUFFIX="${2:-$DB_SUFFIX}"

echo -e "🧼 Migrating warehouse models"

${PHP} artisan fetch:warehouses -d "${DB_SUFFIX}"
${PHP} artisan fetch:warehouse-area -d "${DB_SUFFIX}"
${PHP} artisan fetch:locations -d "${DB_SUFFIX}"
${PHP} artisan fetch:deleted-locations -d "${DB_SUFFIX}"
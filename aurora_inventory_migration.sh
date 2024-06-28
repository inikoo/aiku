#!/bin/bash
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Thu, 13 Jun 2024 06:51:22 Central European Summer Time, Kuala Lumpur, Malaysia
# Copyright (c) 2024, Raul A Perusquia Flores
#

PHP=php
DB_SUFFIX=_base

PHP="${1:-$PHP}"
DB_SUFFIX="${2:-$DB_SUFFIX}"

echo -e "🧼 Migrating inventory models"

${PHP} artisan fetch:stock-families -d "${DB_SUFFIX}"
${PHP} artisan fetch:stocks -d "${DB_SUFFIX}"


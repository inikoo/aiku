#!/bin/bash
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Thu, 13 Jun 2024 06:40:34 Central European Summer Time, Kuala Lumpur, Malaysia
# Copyright (c) 2024, Raul A Perusquia Flores
#

PHP=php
DB_SUFFIX=

PHP="${1:-$PHP}"
DB_SUFFIX="${2:-$DB_SUFFIX}"

echo -e "🧼 Migrating procurement models"

${PHP} artisan fetch:agents -d "${DB_SUFFIX}"
${PHP} artisan org:attach-agent aroma indo
${PHP} artisan fetch:suppliers -d "${DB_SUFFIX}"
${PHP} artisan fetch:deleted-suppliers -d "${DB_SUFFIX}"
${PHP} artisan fetch:supplier-products -d "${DB_SUFFIX}"
${PHP} artisan fetch:purchase-orders -d "${DB_SUFFIX}"

#!/bin/bash
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Thu, 13 Jun 2024 06:58:01 Central European Summer Time, Kuala Lumpur, Malaysia
# Copyright (c) 2024, Raul A Perusquia Flores
#

PHP=php
DB_SUFFIX=_base

PHP="${1:-$PHP}"
DB_SUFFIX="${2:-$DB_SUFFIX}"

echo -e "🧼 Migrating crm models"

${PHP} artisan fetch:customers   -d "${DB_SUFFIX}"
${PHP} artisan fetch:deleted-customers -d "${DB_SUFFIX}"
${PHP} artisan fetch:customer-clients -d "${DB_SUFFIX}"
${PHP} artisan fetch:web-users -d "${DB_SUFFIX}"
${PHP} artisan fetch:prospects -d "${DB_SUFFIX}"
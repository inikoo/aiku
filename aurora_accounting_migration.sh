#!/bin/bash
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Thu, 13 Jun 2024 06:55:45 Central European Summer Time, Kuala Lumpur, Malaysia
# Copyright (c) 2024, Raul A Perusquia Flores
#

PHP=php
DB_SUFFIX=

PHP="${1:-$PHP}"
DB_SUFFIX="${2:-$DB_SUFFIX}"

echo -e "🧼 Migrating accounting models"

${PHP} artisan fetch:payment-service-providers -d "${DB_SUFFIX}"
${PHP} artisan fetch:payment-accounts -d "${DB_SUFFIX}"
${PHP} artisan fetch:payments   -d "${DB_SUFFIX}"
${PHP} artisan fetch:invoices   -d "${DB_SUFFIX}"
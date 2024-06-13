#!/bin/bash
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Thu, 13 Jun 2024 15:09:17 Malaysia Time, Kuala Lumpur, Malaysia
# Copyright (c) 2024, Raul A Perusquia Flores
#

DB_PORT=5432
DB_COLLATE=C.UTF-8
PHP=php

DB_PORT="${1:-$DB_PORT}"
DB_COLLATE="${2:-$DB_COLLATE}"
PHP="${3:-$PHP}"

DB_SUFFIX=_base

echo -e "🧼 Migrating procurement models"

${PHP} artisan fetch:services aw -S awf -d "${DB_SUFFIX}"
${PHP} artisan fetch:products aw -S awf -d "${DB_SUFFIX}"
${PHP} artisan fetch:customers aw -S awf -d "${DB_SUFFIX}" -w web-users
${PHP} artisan fetch:invoices aw -S awf -d "${DB_SUFFIX}" -w transactions
${PHP} artisan fetch:services sk -S euf -d "${DB_SUFFIX}"
${PHP} artisan fetch:products sk -S euf -d "${DB_SUFFIX}"
${PHP} artisan fetch:customers sk -S euf -d "${DB_SUFFIX}" -w web-users
${PHP} artisan fetch:invoices sk -S euf -d "${DB_SUFFIX}" -w transactions
${PHP} artisan fetch:pallets -d _base

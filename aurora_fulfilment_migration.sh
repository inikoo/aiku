#!/bin/bash
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Thu, 13 Jun 2024 15:09:17 Malaysia Time, Kuala Lumpur, Malaysia
# Copyright (c) 2024, Raul A Perusquia Flores
#

PHP=php
DB_SUFFIX=_base

PHP="${1:-$PHP}"
DB_SUFFIX="${2:-$DB_SUFFIX}"

echo -e "🧼 Migrating fulfilment models"

${PHP} artisan fetch:services aw -S awf -d "${DB_SUFFIX}"
${PHP} artisan fetch:products aw -S awf -d "${DB_SUFFIX}"
${PHP} artisan fetch:customers aw -S awf -d "${DB_SUFFIX}" -w web-users
${PHP} artisan fetch:invoices aw -S awf -d "${DB_SUFFIX}" -w transactions
${PHP} artisan fetch:services sk -S euf -d "${DB_SUFFIX}"
${PHP} artisan fetch:products sk -S euf -d "${DB_SUFFIX}"
${PHP} artisan fetch:customers sk -S euf -d "${DB_SUFFIX}" -w web-users
${PHP} artisan fetch:invoices sk -S euf -d "${DB_SUFFIX}" -w transactions
${PHP} artisan fetch:pallets -d _base

#!/bin/bash
# Author: Raul Perusquia <raul@inikoo.com>  
# Created: Thu, 13 Jun 2024 15:10:23 Malaysia Time, Kuala Lumpur, Malaysia
# Copyright (c) 2024, Raul A Perusquia Flores
#

PHP=php
DB_SUFFIX=

PHP="${1:-$PHP}"
DB_SUFFIX="${2:-$DB_SUFFIX}"

echo -e "🧼 Migrating ds models"

#-------------- Dropshipping shortcuts
${PHP} artisan fetch:services aw -S awd -d "${DB_SUFFIX}"
${PHP} artisan fetch:products aw -S awd -d "${DB_SUFFIX}"
${PHP} artisan fetch:customers aw -S awd -d "${DB_SUFFIX}" -w web-users -w clients -w portfolio
${PHP} artisan fetch:invoices aw -S awf -d "${DB_SUFFIX}" -w transactions
#--------------

#-------------- Dropshipping shortcuts
${PHP} artisan fetch:services sk -S dssk -d "${DB_SUFFIX}"
${PHP} artisan fetch:products sk -S dssk -d "${DB_SUFFIX}"
${PHP} artisan fetch:customers sk -S dssk -d "${DB_SUFFIX}" -w web-users -w clients -w portfolio
${PHP} artisan fetch:invoices sk -S dssk -d "${DB_SUFFIX}" -w transactions
#--------------

#-------------- Dropshipping shortcuts
${PHP} artisan fetch:services es -S dse -d "${DB_SUFFIX}"
${PHP} artisan fetch:products es -S dse -d "${DB_SUFFIX}"
${PHP} artisan fetch:customers es -S dse -d "${DB_SUFFIX}" -w web-users -w clients -w portfolio
${PHP} artisan fetch:invoices es -S dse -d "${DB_SUFFIX}" -w transactions
#--------------

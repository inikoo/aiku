#!/bin/bash
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Thu, 13 Jun 2024 08:06:24 Central European Summer Time, Kuala Lumpur, Malaysia
# Copyright (c) 2024, Raul A Perusquia Flores
#

PHP=php
DB_SUFFIX=_base

PHP="${1:-$PHP}"
DB_SUFFIX="${2:-$DB_SUFFIX}"

echo -e "🧼 Migrating procurement models"

${PHP} artisan fetch:orders -d "${DB_SUFFIX}" -w full


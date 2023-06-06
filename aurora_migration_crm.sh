#!/bin/bash
#
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Fri, 26 May 2023 12:44:06 Malaysia Time, Kuala Lumpur, Malaysia
# Copyright (c) 2023, Raul A Perusquia Flores
#

DB=aiku

php artisan fetch:customers "$1" -r -w clients -d _crm
php artisan fetch:deleted-customers "$1" -d _crm
php artisan fetch:web-users "$1" -d _crm
php artisan fetch:prospects "$1" -d _crm
pg_dump -Fc -f "devops/devel/snapshots/au_$1_crm.dump" ${DB}
php artisan fetch:products "$1" -r -d _crm
pg_dump -Fc -f "devops/devel/snapshots/au_$1_products.dump" ${DB}
php artisan fetch:orders "$1" -r -w payments -d _crm
php artisan fetch:invoices "$1" -r -d _crm
php artisan fetch:delivery-notes "$1" -r -d _crm
pg_dump -Fc -f "devops/devel/snapshots/au_$1_sales.dump" ${DB}


#
#  Author: Raul Perusquia <raul@inikoo.com>
#  Created: Tue, 09 Nov 2021 18:23 Malaysia Time, Kuala Lumpur, Malaysia
#  Copyright (c) 2021, Inikoo
#  Version 4.0
#

array=(
employees
deleted-employees
guests
deleted-guests
shops
payment-service-providers
payment-accounts
websites
agents
suppliers
deleted-suppliers
supplier-products
deleted-supplier-products
shippers
warehouses
warehouse-areas
locations
deleted-locations
stock-families
stocks
deleted-stocks
shop-categories
products
services
customers
deleted-customers
web-users
prospects
mailshots
dispatched-emails
email-tracking-events
orders
payments
delivery-notes
invoices
deleted-invoices
)
for i in "${array[@]}";
do
  (
    php artisan fetch:"$i" "$1"
  )
done

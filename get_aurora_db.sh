#!/bin/bash
#
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Fri, 3:36 om 15/3/2024 Malaysia Time, Kuala Lumpur, Malaysia
# Copyright (c) 2023," Raul A Perusquia Flores
#

echo "dumping sql from au origin 🍬"

instances=("indo" "aroma" "es" "sk" "dw")

# shellcheck disable=SC2068
for instance in ${instances[@]};
do

DB="$instance"_base
DUMP_FILE="$DB".sql
echo "🏁 $instance"
echo "    dumping 🥟"
mysqldump --no-tablespaces "$instance" 'Raw Material Dimension' 'Production Part Raw Material Bridge' 'Production Part Dimension' 'User Right Scope Bridge' 'Clocking Machine Dimension' 'Clocking Machine History Bridge' 'Timesheet Dimension' 'Timesheet Record Dimension' 'Customer History Bridge' 'Credit Transaction Fact' 'Deal Dimension' 'Deal Target Bridge' 'Deal History Bridge' 'Deal Campaign Dimension' 'Deal Component Dimension' 'Deal Campaign History Bridge' 'Deal Component History Bridge' 'Deal Component Customer Preference Bridge' 'Inventory Transaction Fact' 'Order Transaction Fact' 'Order No Product Transaction Fact' 'Product Dimension' 'Product History Dimension' 'Product Part Bridge' 'Invoice Dimension' 'Order Dimension' 'Delivery Note Dimension' 'Webpage Type Dimension' 'Customer Client Dimension' 'Attachment Bridge' 'Page Store Dimension' 'Shipping Zone Schema Dimension' 'Shipping Zone Dimension' 'Charge Dimension' 'Customer Fulfilment Dimension' 'Fulfilment Delivery Dimension' 'Fulfilment Rent Transaction Fact' 'Fulfilment Transaction Fact' 'Fulfilment Asset Dimension' 'Customer Dimension' 'Customer Deleted Dimension' 'Prospect Dimension' 'Website User Dimension' 'User Group User Bridge' 'Picking Pipeline Dimension' 'Location Picking Pipeline Bridge' 'Product Category Dimension' 'Shipper Dimension' 'Purchase Order Deleted Dimension' 'Purchase Order Transaction Fact' 'Part Location Dimension'  'Part Category Dimension' 'Category Dimension' 'Category Bridge' 'Part Deleted Dimension' 'Supplier Deleted Dimension' 'Supplier Part Deleted Dimension' 'Warehouse Dimension' 'Warehouse Area Dimension' 'Location Dimension' 'Location Deleted Dimension' 'Agent Supplier Bridge' 'Supplier Dimension' 'Payment Account Store Bridge'  'Payment Dimension' 'Payment Account Dimension' 'Payment Service Provider Dimension'  'Website Dimension' 'Email Campaign Type Dimension' 'Store Dimension'  'Staff Deleted Dimension' 'Image Dimension'  'Image Subject Bridge' 'Staff Role Bridge' 'Account Dimension' 'Account Data' 'Staff Dimension' 'User Dimension' 'Agent Dimension' 'Supplier Data' 'Supplier Part Dimension' 'Purchase Order Dimension' 'Supplier Delivery Dimension' 'Part Dimension' 'pika_fetch' 'pika_fetch_error'  >"$DUMP_FILE"
echo "    loading 🚡"
mysql "$DB" < "$DUMP_FILE"
echo "    compressing 🗜️"
rm "$DB".sql.bz2
pbzip2 "$DB".sql
echo "done 🎉"
done







#
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Tue, 02 May 2023 12:10:46 Malaysia Time, Kuala Lumpur, Malaysia
# Copyright (c) 2023, Raul A Perusquia Flores
#
# test

mysqldump --no-tablespaces dw    'User Group User Bridge' 'Picking Pipeline Dimension' 'Location Picking Pipeline Bridge' 'Product Category Dimension' 'Shipper Dimension' 'Purchase Order Deleted Dimension' 'Purchase Order Transaction Fact' 'Part Location Dimension'  'Part Category Dimension' 'Category Dimension' 'Category Bridge' 'Part Deleted Dimension' 'Supplier Deleted Dimension' 'Supplier Part Deleted Dimension' 'Warehouse Dimension' 'Warehouse Area Dimension' 'Location Dimension' 'Location Deleted Dimension' 'Agent Supplier Bridge' 'Supplier Dimension' 'Payment Account Store Bridge'  'Payment Dimension' 'Payment Account Dimension' 'Payment Service Provider Dimension'  'Website Dimension' 'Email Campaign Type Dimension' 'Store Dimension'  'Staff Deleted Dimension' 'Image Dimension'  'Image Subject Bridge' 'Staff Role Bridge' 'Account Dimension' 'Account Data' 'Staff Dimension' 'User Dimension' 'Agent Dimension' 'Supplier Data' 'Supplier Part Dimension' 'Purchase Order Dimension' 'Supplier Delivery Dimension' 'Part Dimension' > dw_base.sql &
mysqldump --no-tablespaces sk    'User Group User Bridge' 'Picking Pipeline Dimension' 'Location Picking Pipeline Bridge' 'Product Category Dimension' 'Shipper Dimension' 'Purchase Order Deleted Dimension' 'Purchase Order Transaction Fact' 'Part Location Dimension'  'Part Category Dimension' 'Category Dimension' 'Category Bridge' 'Part Deleted Dimension' 'Supplier Deleted Dimension' 'Supplier Part Deleted Dimension' 'Warehouse Dimension' 'Warehouse Area Dimension' 'Location Dimension' 'Location Deleted Dimension' 'Agent Supplier Bridge' 'Supplier Dimension' 'Payment Account Store Bridge'  'Payment Dimension' 'Payment Account Dimension' 'Payment Service Provider Dimension'  'Website Dimension' 'Email Campaign Type Dimension' 'Store Dimension'  'Staff Deleted Dimension' 'Image Dimension'  'Image Subject Bridge' 'Staff Role Bridge' 'Account Dimension' 'Account Data' 'Staff Dimension' 'User Dimension' 'Agent Dimension' 'Supplier Data' 'Supplier Part Dimension' 'Purchase Order Dimension' 'Supplier Delivery Dimension' 'Part Dimension' > sk_base.sql &
mysqldump --no-tablespaces es    'User Group User Bridge' 'Picking Pipeline Dimension' 'Location Picking Pipeline Bridge' 'Product Category Dimension' 'Shipper Dimension' 'Purchase Order Deleted Dimension' 'Purchase Order Transaction Fact' 'Part Location Dimension'  'Part Category Dimension' 'Category Dimension' 'Category Bridge' 'Part Deleted Dimension' 'Supplier Deleted Dimension' 'Supplier Part Deleted Dimension' 'Warehouse Dimension' 'Warehouse Area Dimension' 'Location Dimension' 'Location Deleted Dimension' 'Agent Supplier Bridge' 'Supplier Dimension' 'Payment Account Store Bridge'  'Payment Dimension' 'Payment Account Dimension' 'Payment Service Provider Dimension'  'Website Dimension' 'Email Campaign Type Dimension' 'Store Dimension'  'Staff Deleted Dimension' 'Image Dimension'  'Image Subject Bridge' 'Staff Role Bridge' 'Account Dimension' 'Account Data' 'Staff Dimension' 'User Dimension' 'Agent Dimension' 'Supplier Data' 'Supplier Part Dimension' 'Purchase Order Dimension' 'Supplier Delivery Dimension' 'Part Dimension' > es_base.sql &
mysqldump --no-tablespaces aroma 'User Group User Bridge' 'Picking Pipeline Dimension' 'Location Picking Pipeline Bridge' 'Product Category Dimension' 'Shipper Dimension' 'Purchase Order Deleted Dimension' 'Purchase Order Transaction Fact' 'Part Location Dimension'  'Part Category Dimension' 'Category Dimension' 'Category Bridge' 'Part Deleted Dimension' 'Supplier Deleted Dimension' 'Supplier Part Deleted Dimension' 'Warehouse Dimension' 'Warehouse Area Dimension' 'Location Dimension' 'Location Deleted Dimension' 'Agent Supplier Bridge' 'Supplier Dimension' 'Payment Account Store Bridge'  'Payment Dimension' 'Payment Account Dimension' 'Payment Service Provider Dimension'  'Website Dimension' 'Email Campaign Type Dimension' 'Store Dimension'  'Staff Deleted Dimension' 'Image Dimension'  'Image Subject Bridge' 'Staff Role Bridge' 'Account Dimension' 'Account Data' 'Staff Dimension' 'User Dimension' 'Agent Dimension' 'Supplier Data' 'Supplier Part Dimension' 'Purchase Order Dimension' 'Supplier Delivery Dimension' 'Part Dimension' > aroma_base.sql &
mysqldump --no-tablespaces dw    'Part Deleted Dimension' 'Product Category Dimension' 'Part Dimension' 'Category Dimension' 'Order Transaction Fact' 'Inventory Transaction Fact' 'Order No Product Transaction Fact' 'Product Part Bridge' 'Product Dimension' 'Product History Dimension' 'Customer Fulfilment Dimension' 'Fulfilment Delivery Dimension' 'Fulfilment Rent Transaction Fact' 'Fulfilment Transaction Fact' 'Fulfilment Asset Dimension' 'Order Payment Bridge' 'Customer Client Dimension' 'Website User Deleted Dimension' 'Invoice Deleted Dimension' 'Order Dimension' 'Invoice Dimension' 'Payment Dimension' 'Delivery Note Dimension' 'Store Dimension' 'Customer Dimension' 'Customer Deleted Dimension' 'Prospect Dimension' 'Website User Dimension' > dw_crm.sql &
mysqldump --no-tablespaces sk    'Part Deleted Dimension' 'Product Category Dimension' 'Part Dimension' 'Category Dimension' 'Order Transaction Fact' 'Inventory Transaction Fact' 'Order No Product Transaction Fact' 'Product Part Bridge' 'Product Dimension' 'Product History Dimension' 'Customer Fulfilment Dimension' 'Fulfilment Delivery Dimension' 'Fulfilment Rent Transaction Fact' 'Fulfilment Transaction Fact' 'Fulfilment Asset Dimension' 'Order Payment Bridge' 'Customer Client Dimension' 'Website User Deleted Dimension' 'Invoice Deleted Dimension'  'Order Dimension' 'Invoice Dimension' 'Payment Dimension' 'Delivery Note Dimension' 'Store Dimension' 'Customer Dimension' 'Customer Deleted Dimension' 'Prospect Dimension' 'Website User Dimension' > sk_crm.sql &
mysqldump --no-tablespaces es    'Part Deleted Dimension' 'Product Category Dimension' 'Part Dimension' 'Category Dimension' 'Order Transaction Fact' 'Inventory Transaction Fact' 'Order No Product Transaction Fact' 'Product Part Bridge' 'Product Dimension' 'Product History Dimension' 'Customer Fulfilment Dimension' 'Fulfilment Delivery Dimension' 'Fulfilment Rent Transaction Fact' 'Fulfilment Transaction Fact' 'Fulfilment Asset Dimension' 'Order Payment Bridge' 'Customer Client Dimension' 'Website User Deleted Dimension' 'Invoice Deleted Dimension'  'Order Dimension' 'Invoice Dimension' 'Payment Dimension' 'Delivery Note Dimension' 'Store Dimension' 'Customer Dimension' 'Customer Deleted Dimension' 'Prospect Dimension' 'Website User Dimension' > es_crm.sql &
mysqldump --no-tablespaces aroma 'Part Deleted Dimension' 'Product Category Dimension' 'Part Dimension' 'Category Dimension' 'Order Transaction Fact' 'Inventory Transaction Fact' 'Order No Product Transaction Fact' 'Product Part Bridge' 'Product Dimension' 'Product History Dimension' 'Customer Fulfilment Dimension' 'Fulfilment Delivery Dimension' 'Fulfilment Rent Transaction Fact' 'Fulfilment Transaction Fact' 'Fulfilment Asset Dimension' 'Order Payment Bridge' 'Customer Client Dimension' 'Website User Deleted Dimension' 'Invoice Deleted Dimension'  'Order Dimension' 'Invoice Dimension' 'Payment Dimension' 'Delivery Note Dimension' 'Store Dimension' 'Customer Dimension' 'Customer Deleted Dimension' 'Prospect Dimension' 'Website User Dimension' > aroma_crm.sql &
wait
pbzip2 -f *_base.sql &
pbzip2 -f *_crm.sql &
wait
echo "done 👍"

---
paths:
  - 'resources/js/Components/Procurement/**'
---

# Procurement

## Stock/sales figures follow the ordering-level tab
Any table or modal with Cartons/SKOs/Units tabs (useOrderingLevel) must show stock, sales and coming quantities in the selected level. PurchaseOrderItemStockInfo defaults to SKOs: pass `:inCartons="level === 'cartons'"` wherever a level exists, and format quantities with its formatSales, not formatNumber. HELP-4175: the Cartons tab showed SKO sales because the prop was never passed.

---
paths:
  - 'app/Actions/GoodsIn/**'
---

# Goods In

## Service invoices own the checklist rows they fill
Local container bills (freight, duty, import VAT, other) live in stock_delivery_service_invoices, split per container in stock_delivery_service_invoice_allocations, never on the agent/supplier invoice. SyncStockDeliveryServiceInvoiceCosts writes the checklist through Store/Update/DeleteStockDeliveryCost: each bill type maps to a checklist row through StockDeliveryServiceInvoiceTypeEnum::costType(): shipping = freight + local transport bills on top of the agent invoice's freight (stock_deliveries.data.agent_invoice_freight, never replaced by a bill), duty = customs duty + anti-dumping + other import taxes (org currency), one extra row per bill of any other type (stock_delivery_service_invoice_id). A new bill type only needs a case, a label and its costType; unofficial payments stay in "other", never a type of their own, rows flagged from_service_invoices and locked against hand edits. Import VAT is recoverable and never enters costing (Raul, 9 Oct 2026). A bill touching a costed delivery can't change money until Update costing reopens it.

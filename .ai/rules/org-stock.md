---
paths:
  - 'app/Actions/Inventory/OrgStock/**'
---

# Org Stock

## The unified label is the same in every organisation and never prints with a gap
HELP-3937 (13 Oct 2026). PdfOrgStockLabel with standard=unified delegates to PdfOrgStockUnifiedLabel: unit 100x60 (EAN-13), SKO 100x50 and carton 100x70 (GS1-128, AI 01 GTIN-14 + AI 10 batch). Nothing on it depends on the printing organisation: operators are the constants in GetOrgStockUnifiedLabelData::OPERATORS, warnings are gathered from the products of every organisation. A missing required value goes in blockers and the PDF is refused (422), never printed with a hole; a new required field means a new blocker, not a tick box. GTIN-14 is derived, not stored: indicator 1 SKO / 2 carton, 3-8 for further stocks sharing the unit EAN in stock id order; stocks.barcode and carton_barcode are untouched, so old labels keep scanning. ScanSkoBarcode reads the GS1-128 through parseGs1. The localised labels (tick boxes, Aurora sizes) stay as the exception.

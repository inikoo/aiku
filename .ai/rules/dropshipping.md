---
paths:
  - 'app/Actions/Dropshipping/**'
---

# Dropshipping

## Bundles: derive the whole from the parts, every time

A bundle is one `Product` (`is_bundle`) whose trade units stand in for all of its components.
Everything downstream — available quantity, gross weight, the SKU on the platform variant,
picking — is read off those trade units, so a component missing there is a component missing
from the customer's order. Three ways it goes wrong, all seen in the wild:

- Two components made of the same trade unit. `SyncProductTradeUnits` assigns per trade unit
  (`$tradeUnits[$id] = ...`), so a list carrying the same id twice keeps the last and silently
  drops the rest. Callers must hand it ONE row per trade unit with the quantities already
  added up — it will not do that for you, and this is true of every caller, not just bundles.
- A component that is itself several trade units. The bundle holds
  `component_pivot_quantity * bundle_quantity` of it, not `bundle_quantity`.
- Recomputing from whatever the request happened to mention. After items are created,
  requantified or deleted, read the trade units back off the bundle's CURRENT items
  (`UpdateBundle::getCurrentBundleTradeUnits`). Merging per-branch partials leaves the
  product describing an older component set.

Portfolio `sku` is a snapshot taken at `StorePortfolio` time. Anything that changes a
bundle's components must recompute it via `WithPortfolioSKU::getSKU`, or the platform keeps
selling the old component set. Use `App\Actions\Dropshipping\Bundle\WithBundleTradeUnits`
rather than re-deriving this mapping.

## Platform listed-product payloads share one shape

Every `Get*ListedProducts` / `Search*Products` action feeding the "match to an existing
product" picker must return `id`, `name`, `slug`, `code`, `images` (and `sku_list` where the
platform has variants). The pickers render `item.name` / `item.code` and fall back to the
literal strings "no name" / "no code" — a platform that returns its own vocabulary
(Shopify's `title` / `handle`) renders an entire list of blanks.

## An action returning `[bool, string]` has a result you must check

The Shopify product/variant actions report failure by return value, not by throwing. Calling
one and discarding what it returns turns a rejected push into a 2xx and a "synchronised"
toast, which is indistinguishable to the user from the feature not existing. Propagate it —
`ValidationException` for a retina request — or state in the caller why swallowing is right.

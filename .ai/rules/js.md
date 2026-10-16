---
paths:
  - 'resources/js/**'
---

# Js

## Buttons are white with an icon; the icon carries the colour
Raul, 11 Oct 2026: no filled or coloured buttons (grey, theme or PrimeVue severity fills), in dialogs and confirmations included. Every button has an icon and a white/transparent background with a border: `@/Components/Elements/Buttons/Button.vue` with `type="tertiary"`. Meaning goes in the icon colour through the `#icon` slot: red (`text-red-500`, fal fa-times) for cancel, green (`text-green-600`, fal fa-check) for confirm, amber warning for a destructive confirm.
PrimeVue ConfirmDialog renders its own filled buttons: give it a `#container` slot that draws these buttons (`resources/js/Pages/Grp/Procurement/StockDelivery.vue` is the reference), set `group` on every `confirm.require` so the page's dialog takes it, and a fixed width (~28rem) so long messages wrap instead of stretching.

## Codes and references never wrap
Product codes, SKO codes and PO/delivery references never break across lines in tables or lists: render them with `whitespace-nowrap` (a `#cell(code)` slot when the standard Table would otherwise wrap them). A code split at its hyphen, like "ACEBL-BOX-" / "01", reads as two codes. Let the name or description column wrap instead.

---
paths:
  - 'resources/js/Pages/Grp/Org/Ordering/**'
---

# Ordering

## Order payment box always shows the amount left to pay, also while in the warehouse
Settled after three reversals (24 Aug hid amount and Pay button, 30 Sep HELP-3596 restored Pay, 12 Oct HELP-3896 restored the amount). While an order is in_warehouse / handling / handling_blocked (`isOrderAmountsProvisional`), `NeedToPayV2` keeps BOTH the "Need to pay X of Y" line and the Pay button. What marks the figure as provisional is the grey box, the "In warehouse" heading, the "Amounts will be recalculated when picking is finished" note and the warning above Pay, never hiding the number or the button: staff add products to orders being picked and must know what to charge. The box and the Pay window read the same `box_stats.products.payment.pay_amount` (`IsOrder`), keep it that way.

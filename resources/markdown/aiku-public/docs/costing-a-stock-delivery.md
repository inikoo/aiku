---
title: Costing a stock delivery
summary: Enter what a delivery really cost - the agent or supplier invoice and the bills paid locally for freight, customs and transport - so the stock is valued at its landed cost.
date: 2026-10-12
tags: procurement, stock deliveries, costing, landed cost, service invoices, customs
category: procurement
help_routes: grp.org.procurement.stock_deliveries, grp.org.procurement.service_invoices, grp.org.procurement.dashboard
---

<aside class="tldr">
The cost of a delivery has two parts. The first is the <b>invoice of the agent or supplier</b>: the goods and whatever they charged on top. The second is the <b>landed costs</b>: every bill you pay to somebody else to bring the goods in, such as freight, customs clearance, duty and the lorry to the warehouse. aiku adds the two, shares the landed costs over the items, and values the stock at that total. Enter the costs before the goods arrive: from 12 October 2026 goods can not be put away until they are in.
</aside>

## Where the costing is

Open the delivery under **Procurement → Stock Deliveries**. Near the top of the page there is a table with three rows:

- **Agent invoice** or **Supplier invoice** - what the agent or supplier billed.
- **Landed costs** - what was paid locally to bring the goods in.
- **Landed total** - the two together.

Each row shows its **Status**, its **Amount** in the currency of your organisation and its **Balance due**. When the invoice is in another currency, that amount is shown small underneath. The rows arrive closed; click one to open it and see its lines and its buttons.

Costs can be entered as soon as the delivery is confirmed. You do not have to wait for the goods.

## The invoice of the agent or supplier

Open the invoice row and press **Invoice**. Enter the **Invoice number**, the **Invoice date**, the amount for the **Goods** and each of the agent's or supplier's own charges, marked **Freight** or **Other**. Attach the PDF under **Invoice file** and press **Save invoice**.

Until a real invoice is entered, a delivery that came from the old system shows an **Estimated** invoice built from what was recorded there. Saving the real invoice replaces the estimate, and the estimate is kept for comparison.

The same window holds the **Invoice exchange rate**, the **Settlement** with the deposits applied and the balance due, and the costs read from an attached invoice.

For a delivery bought through an agent, press **Agent payments** to approve the agent's charges and to record what you have paid the agent. The agent's charges only count in the costing once you approve them.

## The landed costs: one line per bill

Open the **Landed costs** row and press **Add** next to **Service invoices**. Enter one service invoice for each bill you received:

- the kind of bill (see the list below),
- who sent it and its reference,
- the date,
- the **currency** and the amount - the freight can be in dollars and the customs agent's bill in euros,
- the PDF of the bill.

One bill that covers several deliveries can be shared between them, by the value of their goods or by amounts you type.

The kinds of bill are:

| Kind | How it is shared over the items |
| --- | --- |
| Freight | by weight |
| Local transport (port to warehouse) | by weight |
| Customs duty | by customs line, or by value when no declaration is entered |
| Anti-dumping duty | the same as customs duty |
| Other import taxes (plastic, excise) | the same as customs duty |
| Import VAT | not costed: it is recovered in the VAT return |
| Customs clearance | by value |
| Port and terminal charges | by value |
| Storage and demurrage | by value |
| Inspection and certificates | by value |
| Documentation fees | by value |
| Insurance | by value |
| Fines | by value |
| Other | by value |

Freight is shared by value instead of by weight when a product in the delivery has no weight recorded; the window tells you which products.

Each bill fills the costing by itself, and a cost filled by a bill can only be changed by changing the bill. All the bills of your organisation are listed under **Service invoices**, reached from the heading of that list, where you can filter them by kind, sender, paid or unpaid, and period.

## Typing a cost without a bill

Press **Edit landed costs** to open the costs as a table. Each cost has:

- **N/A** - tick it when the delivery has no such cost, for example no duty on a purchase inside the country.
- **Amount** and **Currency** - typing an amount marks the cost as received. The currency starts as your organisation's.
- **Rate** - only when the currency is neither the delivery's nor your organisation's.
- **Waiting for invoice** - see below.
- **Invoice** - **Upload** the PDF behind that cost.
- **History** - every change to that cost, with who made it and when.

A note can be added under each cost. **Add extra expense** adds a cost of one of the kinds in the list above; for **Other**, say what it is.

## When a bill has not arrived yet

Tick **Waiting for invoice** on that cost and, if you know, type in how many days you expect it. The delivery page then shows that the total will increase.

If you type an amount while the cost is waiting, it is an **estimate**. An estimate counts in the costing, so the delivery can be costed and the stock valued, and the delivery stays marked **Costed with an estimate** until the real bill replaces it.

## When the costing is finished

A delivery is costed when its invoice is in and its shipping, duty and extra costs each have an amount, an estimate or **N/A**. At that moment the stock put away from the delivery takes its landed cost. The **Landed total** row then lists every cost with its percentage of the goods, and says how much extra cost there is for every euro of goods.

After that the costs are locked. To correct them, or to replace an estimate with the real bill, an accounting manager presses **Update costing**, makes the changes and presses **Finish costing**. The stock is valued again and the change is recorded with their name and reason.

## Costs come before the goods

For deliveries created from 12 October 2026:

- **Putting away is blocked** until the invoice, the shipping and the duty each have an amount, an estimate or **N/A**. The goods can be received and checked, but they can not go onto a shelf, so nothing is sold at the wrong cost.
- **Marking a delivery as received** while costs are missing asks for a reason. The reason is written in the delivery's history and the procurement supervisors are told.

Partner deliveries are not affected: they are costed from the partner's order prices.

## Seeing what still needs costing

The **Procurement** dashboard opens with a bar showing how many deliveries are still to cost, and how many of them are held up. Each figure opens the stock deliveries list on its filter. In the list itself, the **Costing** filter offers **To cost**, **Costs missing, put-away blocked** and **Costed with an estimate**.

## Through your AI assistant

People who cost deliveries can ask their AI assistant to show the costs of a delivery or to save its shipping, duty and extra costs. It shows what will change first and saves only after you confirm. It refuses a delivery that is already costed, and a total of costs higher than the value of the goods unless you confirm, which usually means an amount in the wrong currency. Every save is kept in the AI changes log and can be reverted there. Import VAT, the agent or supplier invoice and the PDF files are entered on the page, not through the assistant.

<aside class="wayfinder"><strong>Where to click in aiku</strong>
<ul>
<li><b>Open a delivery's costing:</b> your organisation → <b>Procurement → Stock Deliveries</b> → the delivery → the table with <b>Agent invoice</b>, <b>Landed costs</b> and <b>Landed total</b>.</li>
<li><b>Enter the invoice:</b> open the invoice row → <b>Invoice</b> → <b>Save invoice</b>.</li>
<li><b>Approve an agent's charges or record a payment:</b> open the invoice row → <b>Agent payments</b>.</li>
<li><b>Add a bill:</b> open <b>Landed costs</b> → <b>Add</b> next to <b>Service invoices</b>.</li>
<li><b>Type a cost, mark it N/A or waiting:</b> open <b>Landed costs</b> → <b>Edit landed costs</b>.</li>
<li><b>Correct a costed delivery (accounting managers):</b> <b>Update costing</b> at the top of the delivery, then <b>Finish costing</b>.</li>
<li><b>See what is waiting:</b> <b>Procurement</b> dashboard, the bar at the top; or the <b>Costing</b> filter of the stock deliveries list.</li>
<li><b>Let someone save costs through their AI assistant (administrators):</b> <b>Sysadmin → Users</b> → open the user → <b>Edit</b> → switch on <b>Can save stock delivery costs through their AI assistant</b>.</li>
</ul>
</aside>

<aside class="permissions"><strong>Permissions you need</strong>
You need permission to view procurement or accounting for the organisation to see the costing, and permission to edit procurement or accounting to enter costs and bills. Changing a delivery that is already costed needs the accounting manager position. Putting goods away and marking a delivery as received follow the warehouse goods-in permissions.
</aside>

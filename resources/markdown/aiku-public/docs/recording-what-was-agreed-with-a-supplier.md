---
title: Recording what was agreed with a supplier
summary: Write down the terms agreed with a supplier or agent - Incoterm, charges, currency, lead time - with the evidence, and add notes and warnings, so they show on every purchase order and survive a change of buyer.
date: 2026-10-16
tags: procurement, suppliers, agents, purchase orders, agreed terms, notes, incidents
category: procurement
help_routes: grp.org.procurement.org_suppliers, grp.org.procurement.org_agents, grp.org.procurement.purchase_orders, grp.org.procurement.supplier_incidents, grp.org.procurement.stock_deliveries
---

<aside class="tldr">
What you agree with a supplier is recorded in two forms. <b>Agreed terms</b> are the commercial points: the Incoterm, what is included in the price, which charges are allowed and at what amount, the currency, the payment terms, the lead time. <b>Notes</b> are everything else a buyer should know: a warning, a specification, an instruction for the agent, an overcharge that happened. Both are entered once on the supplier, and from then on aiku shows them on the supplier, on its products, on the agent and on every purchase order, and prints the ones you choose on the order sent to the supplier.
</aside>

## Where it is

Open the supplier under **Procurement → Suppliers**. The first tab opens with a panel called **What was agreed**: the terms in force, the notes that are pinned or critical, and the number of open incidents. Two more tabs hold the detail:

- **Agreed terms** - every term, the ones in force first, then the ones that were replaced or withdrawn.
- **Notes** - every note written about the supplier and about its products.

The same panel and the same two tabs are on each **supplier product** and on each **agent**. On an agent the panel also has **Suppliers to watch**: the agent's suppliers that carry a warning or an open incident.

## Adding an agreed term

On the **Agreed terms** tab press **Add agreed term** and fill in:

- **Term** - what the term is about: Incoterm, Packing charge, Label charge, Barcode charge, Carton marking charge, Insert or leaflet charge, Mould or tooling charge, Sample charge, Local delivery to port charge, Payment currency, Payment terms, Lead time in days, Minimum order, or Other charge.
- **What was agreed** - **Included** (it is in the price), **Not allowed** (the supplier may not charge it), an amount (**Fixed amount**, **Per unit**, **Per carton**, **Per order**, **Percentage**) or **Text**.
- **Amount**, **Currency** and **Tolerance** - shown when you chose an amount. The tolerance is the difference you accept before it counts as a deviation.
- The text line - the Incoterm and its place (for example FOB Ningbo), what an Other charge is for, or any detail.
- **Agreed on** and **Agreed with** - the date, and the contact at the supplier or agent.
- **Evidence** - the email, chat or document where it was agreed.
- **Who may see it** - **Internal**, **Agent** or **Supplier**.
- **Print on the purchase order** - switch it on for the term to be printed on the order.

A charge with an amount can not be saved without evidence. aiku records you as the person who agreed the term.

<aside class="tip">
For a term to appear on the purchase order it needs both: <b>Print on the purchase order</b> switched on, and <b>Who may see it</b> set to Supplier or Agent. An internal term is never printed.
</aside>

## One organisation or all of them

A supplier is shared by every organisation that buys from it, and so is a term: what one buyer records is in force for all of them. When your organisation has a different deal, switch on **Only for this organisation**. Your organisation then sees its own term, and the others keep the shared one.

## A term for one product

A term entered on a **supplier product** replaces the supplier's term of the same kind for that product only. The product page shows where each term comes from: **This product**, **Supplier** or **Agent**. On the product's **Agreed terms** tab, the supplier's term that it replaces is marked **Overridden**.

## Changing or withdrawing a term

Terms are never edited or deleted. Press **Change** on a term, enter what is now agreed, and save: the new term is in force and the old one stays in the list as **Superseded**, with the date it stopped. Press **Withdraw** when a term no longer applies and nothing replaces it.

A term can be changed or withdrawn by the person who agreed it and by the procurement supervisor.

## Notes

On the **Notes** tab press **Add note**. Write one line for the title and, if needed, the detail underneath. Then choose:

- the **category**, which gives the note its colour;
- the **severity**: Info, Warning or Critical;
- who may see it: Internal, Agent or Supplier. A new note is internal;
- **Pin: show on purchase orders**, for a note every buyer must see when ordering.

| Category | Colour | Use it for |
| --- | --- | --- |
| Agent | Purple | Instructions for the agent about this supplier |
| Product | Blue | Agreed specification, material, size, finish |
| Packaging | Green | Inner and outer packing, labels, inserts |
| Charges | Red | Agreed or disputed charges |
| Delivery | Orange | Lead times, seasonal delays, shipping |
| Currency and payment | Yellow | Currency, deposit, payment method |
| Incident | Black | An overcharge or a wrong delivery that happened |
| General | Grey | Anything else, and the notes written before categories existed |

For an **Incident** you can enter the amount overcharged or claimed and its currency. An incident starts as open and counts in the panel until it is resolved.

Notes are not deleted either. Press **Resolve** when a note no longer applies; it stays in the list, crossed out. **Pin** and **Unpin** move a note in and out of the panel. The author of a note and the procurement supervisor can pin and resolve it.

## On a purchase order

Open a purchase order of the supplier. Above the lines, the **What was agreed** panel shows the terms in force for the supplier and for the products on the order, and the pinned or critical notes on the order, the supplier, its agent and those products.

When one of those notes is **Critical**, tick **I have read the critical notes for this order** before pressing **Submit**. Without the tick the order is not submitted.

The **Notes** tab of a purchase order and of a stock delivery has the same form as the supplier's, so an incident can be written on the order where it happened; it is then linked to that order.

## What the supplier and the agent see

The purchase order PDF carries an **Agreed terms** block and a **Notes** block, and the email that sends it lists the agreed terms. They contain only what is marked to be seen by the supplier and, for an order placed through an agent, by the agent. Internal terms and notes are never printed.

An agent that works in aiku sees, on its own pages, only the terms and notes marked as visible to the agent, and can not add or change any.

## Handing a supplier over

On the supplier, press **Handover sheet** in the **What was agreed** panel. It opens a PDF with every term in force and every open note, to give to the person taking the supplier over.

## When an order or an invoice differs from what was agreed

aiku compares what is ordered and invoiced with the agreed terms, for every supplier or agent with at least one agreed term recorded. A supplier or an agent cannot raise a price or add a charge on its own: every difference waits for purchasing to accept it.

- **On a purchase order:** a line that costs more than the supplier product's cost, or an order in another currency than the agreed payment currency.
- **On the supplier's or agent's invoice of a delivery:** a charge whose term is Included or Not allowed, a charge above the agreed amount, a charge with no agreed term, or freight when the agreed Incoterm (CFR, CIF, CPT, CIP, DAP, DPU, DDP) has the supplier pay it.

When you enter the invoice, give each charge its type (Packing charge, Label charge, Freight and so on) so it is checked against the right term.

Each difference is listed in red in a **Different from what was agreed** panel at the top of the order or the delivery, next to what was agreed. Type why you accept it and press **Accept**. Until every difference is accepted the order cannot be submitted, the delivery cannot be paid and the agent's charges cannot be approved. If the order or invoice changes afterwards, the new difference must be accepted again.

Every accepted difference becomes an open **Incident** note on the supplier or agent, with the amount and your reason, and the procurement supervisor and the buyer of the order are notified. The supplier lists show the number of open incidents next to the supplier's code.

## Checking a delivery as it arrives

From the moment a stock delivery is dispatched, it has a **Checklist** tab, on the procurement page of the delivery and on the goods in page of the warehouse. It lists, for each supplier on the delivery and then for each of its products:

- **Packing** - who packs the goods and in what material, when it is recorded on the supplier or on the product;
- every open note of the **Packaging** or **Product** category written on that supplier or product.

The number on the tab is how many checks are still to do. Press **Passed** when the goods match. Press **Failed** when they do not: choose the issue type, enter the quantity affected if you know it, write what is wrong and press **Open incident**. aiku opens an **Incident** note on that supplier or product, linked to the purchase order the goods came on, and the check shows who failed it and when.

Goods in staff can tick the checklist and can write an **Incident** on the **Notes** tab of a stock delivery. They can not write notes of any other category.

## Late deliveries

When a delivery is received, aiku measures each of its purchase orders from the day it was submitted to the day the delivery was received, and compares it with the **Lead time in days** agreed with the supplier, or with its agent. An order more than 7 days later than agreed gets an **Incident** of type **Late** on the supplier, linked to the order, saying how many days it took. It is written once per order. A supplier with no agreed lead time is not measured.

## Supplier incidents report

The **Supplier incidents** card on the procurement dashboard, which counts the open incidents, opens a report that lists each supplier or agent with incidents for your organisation: how many, how many are still open, the amount disputed and the amount recovered, with the date of the last one. An incident on a supplier product or on a purchase order counts for its supplier. The amounts are in the group currency, converted on the day the incident was written. **Recovered** is the amount of the incidents whose outcome was set to **Credited**. Press the supplier's code to open its notes.

<aside class="wayfinder"><strong>Where to click in aiku</strong>
<ul>
<li><b>See what was agreed with a supplier:</b> your organisation → <b>Procurement → Suppliers</b> → the supplier → the <b>What was agreed</b> panel.</li>
<li><b>Add a term:</b> the supplier → <b>Agreed terms</b> → <b>Add agreed term</b>.</li>
<li><b>Change or withdraw a term:</b> the supplier → <b>Agreed terms</b> → <b>Change</b> or <b>Withdraw</b> on its row.</li>
<li><b>Add a note:</b> the supplier → <b>Notes</b> → <b>Add note</b>.</li>
<li><b>Pin or resolve a note:</b> the supplier → <b>Notes</b> → <b>Pin</b> or <b>Resolve</b> on its row.</li>
<li><b>A term or a note for one product:</b> the supplier → its products → the product → <b>Agreed terms</b> or <b>Notes</b>.</li>
<li><b>Acknowledge critical notes:</b> the purchase order → tick <b>I have read the critical notes for this order</b> → <b>Submit</b>.</li>
<li><b>Print the handover sheet:</b> the supplier → <b>What was agreed</b> panel → <b>Handover sheet</b>.</li>
<li><b>Check a delivery:</b> the stock delivery → <b>Checklist</b> → <b>Passed</b> or <b>Failed</b> on each line.</li>
<li><b>See incidents per supplier:</b> <b>Procurement</b> dashboard → the <b>Supplier incidents</b> card.</li>
<li><b>Accept a difference:</b> the purchase order or stock delivery → <b>Different from what was agreed</b> → type the reason → <b>Accept</b>.</li>
</ul>
</aside>

<aside class="permissions"><strong>Permissions you need</strong>
You need permission to view procurement for the organisation to see terms and notes, and permission to edit procurement to add a note or a term. Changing or withdrawing a term agreed by someone else, and pinning or resolving a note written by someone else, need the procurement supervisor position. Accepting a difference from the agreed terms needs permission to edit procurement for the organisation that pays. Goods in staff with permission to edit goods in for one of the organisation's warehouses can tick the delivery checklist and report incidents.
</aside>

---
title: Recording what was agreed with a supplier
summary: Write down the terms agreed with a supplier or agent - Incoterm, charges, currency, lead time - with the evidence, and add notes and warnings, so they show on every purchase order and survive a change of buyer.
date: 2026-10-14
tags: procurement, suppliers, agents, purchase orders, agreed terms, notes, incidents
category: procurement
help_routes: grp.org.procurement.org_suppliers, grp.org.procurement.org_agents, grp.org.procurement.purchase_orders
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
</ul>
</aside>

<aside class="permissions"><strong>Permissions you need</strong>
You need permission to view procurement for the organisation to see terms and notes, and permission to edit procurement to add a note or a term. Changing or withdrawing a term agreed by someone else, and pinning or resolving a note written by someone else, need the procurement supervisor position.
</aside>

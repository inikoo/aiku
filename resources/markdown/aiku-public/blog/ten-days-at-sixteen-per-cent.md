---
title: Ten days at sixteen per cent
summary: We built reply suggestions for the customer service chat inbox, set a goal of 60% close to what staff really sent, and spent ten days between 10 and 20%. Better writers, a critic, 23,000 past replies as examples and a coverage gate did not move the live number. We stopped. For a drafter that gets one shot, the technology at a cost that makes sense is not there yet, so the next attempt puts each staff member's own AI assistant on the conversation instead.
date: 2026-10-11
tags: ai, chat, mcp, production
---

<aside class="tldr"><strong>TL;DR</strong>The chat inbox showed customer service a suggested reply for each customer message. We measured it against what the agent really sent: 16% close on the first day, with a goal of 60%. In ten days we added a chain of writers with a critic, similar past replies as examples, rules learned from years of email, fixes for the facts it got wrong and a gate that hid the weak ones. A replay of one day's chats reached 26%. Live, the same chain gave 10% the next day and 16% over the week, and staff used or edited between 5 and 11% of the cards. What we ran cost $17 a month, so money was never the problem with it. The problem is the shape: one shot, with the facts we chose beforehand. The shape that would work is a frontier model looking things up for every message, and at API prices that is overkill for what it returns. We had agreed a rule before starting: under one in five used by mid-October, switch it off. We are under it. The next attempt connects the assistant each person already has on subscription to the conversation, through the same kind of connection staff already use to ask the business questions.</aside>

## The card

When a customer writes to one of our shops, by website chat, WhatsApp or email, the message lands in one inbox. For some weeks that inbox also showed a card with a suggested reply.

We were careful about how it was made. Code looks up the facts: the customer's orders and deliveries, stock, the shop's policies, notes the team wrote. The model only phrases them. A decision the model cannot take, such as whether to refund, is left as a gap the agent has to fill before the reply can be sent. The agent can use the card, edit it or discard it, and nothing is ever sent without a person.

## Two numbers

Counting clicks on Use is too strict. An agent who reads the card, agrees and types the same thing in their own words has been helped and shows up as a discard. So we kept two numbers.

The strict one: the share of cards used or edited. The fair one: the share of cards close to what the agent sent afterwards, scored from 0 to 3 by the same System One model that sorts [the customers in the spam folder](/blog/the-customers-in-the-spam-folder), with 2 or more counting as close.

The goal was 60% close. Before starting we also wrote down when to stop: if fewer than one card in five was being used or edited by the middle of October, the suggestions would be switched off.

## Ten days

| When | What changed | Chats scored | Close to staff |
|---|---|---|---|
| 1 Oct | Live baseline: one cheap writer | 127 | 16% |
| 1 Oct, replayed | A chain of writers with a critic and rewrites, plus similar past replies as examples | 127 | 26% |
| 2 Oct | The same chain, live | 58 | 10% |
| 5 Oct | Fixes for claims, replacement deliveries and products named in words | 50 | 18% |
| 6 to 7 Oct | Card shown only when the facts cover most of the answer | 51 | 20% |
| 1 to 7 Oct | The whole week | 666 | 16% |

The strict number stayed between 5 and 11% the whole time.

Some of the work did what it was meant to. On the first day 87% of cards carried a gap; the new chain brought that to 22%. The rewrite loop was worth having: without it the replay fell from 23% to 19%. Searching 23,000 past email and chat replies for similar cases added a few points. Reading the archive of old email for rules the team follows found real ones, such as crediting a missing item against the next order.

None of it moved the number that mattered.

## The replay that flattered us

Each change was tested by replaying a day of real conversations through the new chain and scoring the result against what the agent had sent. That is how we got to 26%.

The next day, live, the same chain scored 10%. With samples of 50 to 130 chats the noise is about five points either way, so the replay was never as good as it looked and the live day was never as bad. But the direction was clear: a gain measured on one day's conversations did not carry over to the next day's.

Two things the replay could not show us. Agents often send a holding reply first, "let me check and come back to you", while the card tried to give the whole answer. And cards promised things nobody had done: that production had been asked, that a colleague had been told.

## Staff were right

When uptake is low, the easy explanation is that people are not using the tool. We checked. Over the week, only 10 cards that were close to the eventual reply had been discarded. Where staff rarely used the card, the card was usually wrong. Uptake by shop ran from 4% on the worst two to 40% on the best, a dropshipping shop where questions are narrower and the answers are in the data.

About a third of the misses were not writing problems at all. The agent knew something the writer had not been given: that a payment proof needed checking, that a product is only sold as a full set, that the customer should be sent to another shop.

## A better writer in the same chain

The obvious fix is a stronger model. We had one in the chain, a frontier class model, meant as the writer of last resort. By an accident of how conversations were split between writers, it wrote first for 43 of 98 cards on two days. It was close on 5 of the 34 that could be scored. The cheap writers did no worse.

So the writer was not the limit. The limit was the shape of the job. The drafter gets one shot, with the facts we picked for it before it started. It cannot open the delivery and see that the parcel was a replacement. It cannot search for a product the customer described and did not name. It cannot ask the agent what they want to say. Every one of those we could patch, and we patched several, and each patch was us guessing in advance what the model would need.

## Where the cost really is

What we ran was cheap: about $5 a month for all shops with one writer, about $17 with the chain. Cost was never the problem with it.

The cost is in the version that would work. That version is a model that reads the conversation, decides what to check, runs the lookups, reads the results and checks again. It needs a frontier model, because cheaper ones lose the thread over many steps. It would run on every incoming message, around 290 a day, whether or not the agent wanted help with that one. And we would build and maintain the loop around it. We did not price it to the cent. Per token it is a different order of cost from $17 a month, for a card that may still be ignored.

So the plain statement is this: for reply suggestions, the technology at a cost that makes sense is not there yet. Cheap and one shot is not good enough. Good enough is overkill.

## What we are doing instead

The strongest models, with the loop already built around them, are sold to people on a flat monthly subscription. Our staff already use them to ask the business questions, through [the tools we expose over the Model Context Protocol](/blog/rag-is-dead-give-the-model-the-tools).

The next attempt extends that to customer service. The agent's own assistant gets three things: the conversations waiting, one conversation with everything aiku knows about it, and a way to save a draft reply onto it. The draft appears in the same card. A person reads it and sends it from aiku.

We have not built it yet. These are the rules it will be built to:

- **Any assistant.** It has to work from whichever provider's assistant a person uses. That means tools only, with nothing that depends on one vendor's features, and the shop's rules returned together with the conversation, so no setup is needed on the assistant's side.
- **The server is the guard.** A customer's message is text written by a stranger, read by a model that holds tools. "Credit my account" inside a chat is an instruction aimed at the assistant. So this connection cannot send a message, refund, credit, or change an order or a customer. We do not rely on the assistant asking for confirmation.
- **Opt in per person.** An administrator switches it on for each member of staff, the same way as every other write we have given assistants.
- **The same yardstick.** Drafts saved by an assistant are scored exactly as the cards were, so the two can be compared.

What changes is who is in the loop. The agent asks only when they want help, says what the reply should say, and the assistant goes to find what it needs.

## What stays

The pieces that work stay on cheap models: sorting spam and noise, summaries, flagging urgent messages, the out of hours reply, the checklist for claims. The lookups written for the drafter become what the assistant reads. The rules learned from old email become the rule book it receives. The scoring stays.

The suggestions themselves are cut back to the few that stand on something aiku looked up, a little wider on the dropshipping shops where they earned their place.

## What we would keep

Write the stopping rule before you start. Ours was one line, agreed on a day when we still expected to pass it. Without it we would be tuning prompts in November.

Score against what people did, not only what they clicked. The click number told us the cards were ignored. The other number told us they deserved to be.

Do not trust a replay of yesterday for tomorrow. It is the right tool for choosing between two versions and the wrong one for predicting a live figure.

Before blaming adoption, check whether the people are right. Ours were.

And when a model keeps missing because it was not handed something, adding one more thing to hand it is a patch. The fix is to let it look.

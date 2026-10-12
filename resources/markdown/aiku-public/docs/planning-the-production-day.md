---
title: Planning the production day
summary: For the production manager and the floor supervisor - putting jobs on a day's plan without naming anyone, giving each step to an operator, and what happens to work not finished on its day.
date: 2026-10-12
tags: production, planning, floor
category: production
help_routes: grp.org.productions.show.operations.day_plan
series: Ordering from partners
order: 13
---

<aside class="tldr">
Two people, two jobs. The <b>manager</b> decides what is made on a day: on the <b>To produce</b> board she picks the cards and presses <b>Plan for this day</b>, without naming anyone. The <b>supervisor</b> opens <b>Operations → Day plan</b> and gives every step of every planned job to an operator. Each operator then sees their own steps on the floor screen. Anything not finished on its day stays on the plan in amber until someone confirms it for a new day.
</aside>

## The manager plans the day

On **Factory → To produce**, the board works as before: cards go from **Backlog** to **Preparing**, where the quantity is set, and from **Preparing** to **Assigned**, where the job order is created.

When a card is dropped on **Assigned**, the small window that asks *Who makes it?* now starts with a date and a button:

1. Pick the day. It opens on today.
2. Press **Plan for this day**.

A job order is created for that day with nobody named on it. Each card becomes its own job order, so each can be ordered and moved on its own. Several cards selected and dropped together are planned together, one job order each.

Picking a name from the list below still works as it always did: the whole job goes to that person and is not on any day's plan.

A job that already has someone named can be put on a plan too: press the pencil next to the name on its card and then **Plan for this day**. The name is taken off the job and the supervisor gives out its steps.

## The supervisor gives out the steps

**Factory → Operations → Day plan** lists the jobs planned for the day, one row per product on a job.

| Column | Meaning |
| --- | --- |
| Order | the place of the job in the day - type a number to move it |
| Job | the job order and its state |
| Product | what is made |
| Qty | how many |
| Steps and operators | every step of the recipe: made so far / asked for, and who it is given to |
| Planned | the day it was planned for |

Each step has its own list of names. Choose one and that step appears on that person's floor screen, and on nobody else's. A step with an amber border has nobody yet; the bar at the top counts them, *3 steps without an operator*, so nothing is left out by mistake.

To change who does a step during the day, choose another name. To take a step back from someone, press the small cross in the list. If the person had already started, the work they did stays recorded under their name: the list only decides who sees the step from now on.

The date at the top moves to another day. Tomorrow's plan can be given out today.

## Following the day

The same page shows how the day is going, and refreshes on its own:

- the bar at the top: how many jobs, *12 of 18 steps done*, steps without an operator, jobs not finished on their day;
- beside each step: a grey circle (not started), an amber spinner (started) or a green tick (done), with the count made so far;
- *working now* and the name, when someone has the step open on the floor.

People who can see the page but do not run the floor see the names, not the lists.

## When the supervisor is away

The manager opens the same page and gives out the steps herself. Planning and giving out steps need the same position.

## What was not finished

A planned job with steps still open after its day is not dropped. From the next day on it shows on the Day plan with its old date in amber, and the bar counts it as *not finished on its day*. For each one, choose:

- **Confirm for** the day you are looking at - the job moves to that day's plan, at the end of the order;
- **Take off the plan** - the job stays as a job order but is on no day's plan.

Until one of the two is pressed, the job stays in front of you every day. Its steps also stay on the floor screens of the operators they were given to, so someone who paused a mix yesterday can carry on this morning.

When an operator closes a job short and moves the rest to a new job (see [Working the floor screen](/docs/working-the-floor-screen)), the new job keeps the planned day, the place in the order and the operator of each step.

<aside class="wayfinder"><strong>Where to click in aiku</strong>
<ul>
<li><b>Plan a job for a day:</b> <b>Factory</b> → <b>To produce</b> → drop the card on <b>Assigned</b> → pick the day → <b>Plan for this day</b>.</li>
<li><b>Give a step to an operator:</b> <b>Factory</b> → <b>Operations</b> → <b>Day plan</b> → the list beside the step.</li>
<li><b>Change the order of the day:</b> type a number in <b>Order</b>.</li>
<li><b>Yesterday's unfinished job:</b> <b>Confirm for</b> the day, or <b>Take off the plan</b>.</li>
<li><b>From the board to the plan:</b> the <b>Day plan</b> link above the board.</li>
</ul>
</aside>

<aside class="wayfinder"><strong>Permissions you need</strong>
<ul>
<li>Positions are set on the employee record under Human Resources and carry the rights with them.</li>
<li>Planning a day, giving out steps, changing the order: <b>Floor supervisor</b> for the factory.</li>
<li>Seeing the Day plan without changing it: <b>Foreman</b>.</li>
<li>Working a step given to you: <b>Operative</b>, on the floor screen.</li>
</ul>
</aside>

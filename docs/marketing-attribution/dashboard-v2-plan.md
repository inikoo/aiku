# Marketing dashboard v2: plan

Status: phases 1–4 built on 21 October 2026. Phase 5 is still to do.

A second marketing dashboard that compares attribution models side by side, starting with
**linear vs first touch**. The current dashboard stays exactly as it is. The new page is reachable
by URL only and has no entry in the shop navigation.

The visual reference is the "Aiku Attribution Dashboard" design canvas (three artboards:
Acquisition, Orders, Journey). Its layout is followed. Its sidebar menu is not: the page uses the
standard Aiku `Tabs`, and its colours come from the Aiku theme.

## Goals

- Show how credit moves between channels when the model changes, per channel, in orders and revenue.
- Give paid channels a fair reading on B2B shops, where a buyer finds us through an ad and orders
  days later through search or by typing the address.
- Leave room for more models (U-shaped, W-shaped) without reworking the page.

## Non-goals

- No change to the stored attribution. `model_has_traffic_sources` keeps its linear shares, and
  the existing dashboard, listings, hydrators and `marketing_*` views read it as before.
- No navigation entry, no settings screen, and no per-shop model choice.
- No comparison with Google Ads' own data-driven attribution yet. We hold no Google conversion
  data, so the "three lenses" panel in the design is left out.

## Route and access

| | |
| --- | --- |
| URL | `/org/{organisation}/shops/{shop}/marketing/dashboard-v2` |
| Route name | `grp.org.shops.show.marketing.dashboard_v2`, in `routes/grp/web/org/shops/marketing.php` |
| Action | `App\Actions\UI\Dropshipping\Marketing\ShowMarketingDashboardV2` (an `OrgAction`) |
| Page | `resources/js/Pages/Grp/Org/Marketing/MarketingDashboardV2.vue` |
| Tabs | `App\Enums\UI\Marketing\MarketingDashboardV2TabsEnum`: `acquisition`, `orders`, `journey` |
| Permission | Same as the current dashboard: `marketing.{shop}.view` |
| Period | `WithMarketingPeriod`, the same saved interval and `DashboardSettings` bar as the current dashboard |

`GetShopNavigation` is not touched. Each tab's prop is loaded only for the open tab
(`Inertia::optional` otherwise), as the current dashboard does.

## How credit is calculated

Attribution is computed **at read time** from the raw touch history in `customers.traffic_sources`,
parsed with `ParseTrafficSourceTouches`. Nothing is written. Every model reads the same touches, so
the comparison is like for like, and adding a model means adding one class.

### The unit is the order

Orders placed in the period are counted: state not `creating` or `cancelled`, not deleted, and
placed at `COALESCE(orders.submitted_at, orders.date)`, the same `ORDER_PLACED_AT` rule as
`WithTouchCausality`. Revenue is `orders.net_amount`, in the shop's currency, including orders not
yet invoiced.

Each order is split between the touches its customer had **before the order was placed**. Every
order's shares sum to 1.00. An order with no qualifying touch goes to **No touch**, so channels plus
No touch always add up to the number of orders. This is the "Credit check" on the Orders tab.

### Models

| Model | Split of one order |
| --- | --- |
| `linear` | Evenly over every unique channel + campaign pair whose touches qualify under the attribution window: the first touch of the pair is before the order, and the order is no later than the pair's last touch + the shop's window (90 days by default, `GetAttributionWindow`). This matches today's linear reading, measured per order instead of per invoice. |
| `first_touch` | 100% to one touch per customer. For a **new** customer it is the acquisition touch (below), and it claims orders placed within **365 days** of that touch. For an **existing** customer it is the first recorded touch, and it claims orders placed within **90 days** of it. In both cases the touch must come before the order. |

### Acquisition touch: who brought the customer in

The acquisition touch is the **earliest touch in the 90 days before registration**, from
`customers.created_at − 90 days` to `customers.created_at`.

- A touch earlier than that is ignored. If the very first touch was 120 days before registration,
  the earliest touch still inside the 90 days takes its place.
- Touches after registration never acquire anyone.
- Example: a customer clicks an ad today and registers through organic search two days later. The
  customer is acquired by the ad, because the ad is the earliest touch within the 90 days. Organic
  search still shares the credit under linear, but it is not the channel that brought the customer
  in.

A customer with an acquisition touch is **new**. A customer without one is counted as **No first
touch**: no touch at all in the 90 days before registering, or registered before tracking began
on 7 August 2026. For the orders of such a customer, `first_touch` falls back to their first
recorded touch, with the 90-day window of an existing customer.

### Engine

```
app/Enums/CRM/TrafficSource/AttributionModelEnum.php   linear, first_touch
app/Actions/CRM/TrafficSource/Attribution/
    SplitOrderAttribution.php      (touches, registered at, order placed at, model) → shares; acquisition touch
    GetShopOrderAttribution.php    Orders tab: every order in the period under every model
    GetShopAcquisitionCohorts.php  Acquisition tab: registrations, buyers, windows, monthly cohorts
    GetShopOrderJourney.php        Journey tab: one order and the customer's other orders, touch by touch
```

`GetShopOrderAttribution` loads the orders in the period, then their customers' touch strings in
chunks, and runs every model over each order. The Orders and Acquisition results are cached per shop
and period for 10 minutes. Touches with no timestamp, Direct touches and referral hosts that fail
validation are left out, as `AttachTrafficSourcesToModel` does. A long period on a large shop parses tens of thousands of touch strings, so
once U- and W-shaped exist the shares should be precomputed into an `order_attribution_shares` table
when an order is submitted. That is a later phase.

## Tab 1 · Acquisition, "who brought them in?"

The cohort is the customers who **registered** in the period. Each one counts whole for the channel
of their acquisition touch, or for No first touch when they have none. This tab does not depend on the
model choice.

- **KPIs:** registrations; new buyers (at least one submitted order) and their rate; brought by
  ads (share of registrations whose acquisition touch is a paid channel, and their buyers); paid CAC
  (ad spend in the period ÷ new buyers from paid channels); No first touch (registrations with no touch in
  the 90 days before registering).
- **Buyers by acquisition channel:** donut and legend per channel, No first touch included.
- **CAC per buyer:** one bar per paid channel.
- **Revenue per buyer:** per channel, with a 30 / 90 / 180 / 365 days since registration toggle. A
  window the cohort has not fully lived through shows "Not reached yet" instead of a partial figure.
- **Cohort growth:** for one selected channel (default Google Ads), one row per registration month
  and columns at 30 / 90 / 180 / 365 days, over the last six months since tracking began. A cell is
  **Final** when every customer of the month is past the window, **Growing** when the oldest has at
  least passed the previous window, and **Not yet** otherwise (it would only repeat the shorter
  window). The Revenue per buyer toggle uses the same states.

## Tab 2 · Orders, "who closed the sale?"

- **KPIs:** orders split into first and repeat (an order is *first* when it is the customer's first
  submitted order ever); paid ads credit change from linear to first touch, in orders; credit check
  (Σ shares = order count); no touch (count and share).
- **Linear vs first touch:** one row per channel with a thin bar for linear and a thick bar for
  first touch, an Orders / Revenue toggle, and the change in %.
- **Paid channels, first touch:** one card per paid channel with ROAS (first-touch revenue ÷
  spend), cost per order, spend and orders.
- **First vs repeat orders:** for each channel, how its first-touch credit divides between first
  orders and repeat orders.

Spend is the period's `traffic_source_costs`, read the way `GetShopMarketingOverview` reads it.

## Tab 3 · Journey, "every touch counts, some more"

- **Find order:** an order reference search within the shop. The URL carries it (`?order=…`) so a
  journey can be linked to.
- **Header:** the customer, the channel that acquired them (or No first touch), the acquisition-touch
  date, registration date, and their number and value of orders.
- **Per order** (the searched one, plus the customer's other orders, newest first): the touches up to
  that order on a timeline. Each touch is tagged Acquisition / Order where it applies, and shows its
  share under first touch and under linear. Below the timeline, a stacked bar per model. Touches outside the 90-day
  acquisition range are drawn faded.
- **Rules:** a short panel stating the active rules: models, windows, the 90-day acquisition range,
  new vs existing, and Σ 1.00 per order.

## Look and feel

- `PageHeading` and Aiku `Tabs`, with `DashboardSettings` for the period, as on the current dashboard.
- Interactive colour from the organisation theme: `--app-accent`, `-strong`, `-soft`, `-muted`,
  `-deep`, `-text`. This applies to KPI highlights, the selected toggle and the model badge.
- Channel colours are a fixed categorical palette that carries meaning, defined once in a shared
  TypeScript map so every chart and legend agrees. Green and red stay for good and bad (ROAS ≥ 1 or
  < 1); amber is for No touch.
- PrimeVue controls: `SegmentedToggle` for the Orders / Revenue and window toggles, `Select` for the
  cohort channel, and `InputText` or `AutoComplete` for the order search. Every string goes through
  `ctrans`.
- Charts follow the design: a CSS conic-gradient donut and CSS bars. No new dependency.

## Files

| Path | What |
| --- | --- |
| `app/Actions/UI/Dropshipping/Marketing/ShowMarketingDashboardV2.php` | Page action, props per tab |
| `app/Enums/UI/Marketing/MarketingDashboardV2TabsEnum.php` | The three tabs |
| `app/Enums/CRM/TrafficSource/AttributionModelEnum.php` | The models |
| `app/Actions/CRM/TrafficSource/Attribution/*` | Splitter and the data of each tab |
| `routes/grp/web/org/shops/marketing.php` | One `GET dashboard-v2` route |
| `resources/js/Pages/Grp/Org/Marketing/MarketingDashboardV2.vue` | Page with tabs |
| `resources/js/Components/Marketing/V2/*` | `AcquisitionTab`, `OrdersTab`, `JourneyTab`, and `palette.ts` with the chart and channel colours |

## Phases

1. Route, action, tabs enum and an empty page with the period bar.
2. Engine (`linear`, `first_touch`) and the Orders tab.
3. Acquisition tab.
4. Journey tab.
5. Later, out of scope for now: more models such as U-shaped and W-shaped; precomputed
   `order_attribution_shares`; Google Ads data-driven comparison.

## Known limits

- Touches exist only from 7 August 2026, so a 365-day window cannot fill until August 2027. Cohort
  cells show Growing or Not yet until then.
- Anonymous journeys across devices are lost until the customer logs in, and Direct visits are not
  touches. Both end up in No touch.
- Results follow the touch history as it is today. A login that merges a device's earlier touches
  into a customer can change the split of their past orders. This lasts until shares are
  precomputed at submission (phase 5).

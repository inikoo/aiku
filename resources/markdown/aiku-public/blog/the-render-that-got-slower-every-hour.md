---
title: The render that got slower every hour
summary: For as long as the storefront has rendered on the server, a page missing from the cache cost about a second, and we kept blaming the size of the page. It was a translation helper. Every translated word left a small reactive effect behind in the rendering process, nothing ever cleaned it up, and every page drawn in a different language re-ran all of them. How a cost that grew with uptime gave it away, a heap diff full of translation keys, the twenty-line fix, and what changed once a cache miss cost 36 ms instead of 1.2 s.
date: 2026-10-18
tags: ssr, vue, performance, storefront, varnish, caching
---

<aside class="tldr"><strong>TL;DR</strong>Uncached storefront pages spent about a second in the server-side render, 1.2–1.5 s on our main web server. The real cost of a render was 25 ms. The rest came from our translation helper. It called the i18n library's <code>trans()</code>, which creates a <code>watchEffect</code> and a <code>computed</code> on every call. Called from a template on the server, those effects belong to no component, so nothing ever stopped them. They hung off one translation store shared by the whole process, and each page drawn in a different language re-ran the whole pile. Looking messages up directly fixed it: renders are now 36 ms at the median and stay there. Along the way we switched the render process to production mode, cut its workers from 32 to 8 (about 35 GB down to about 2 GB), stopped Varnish keeping pages drawn without the server, and shrank the cache warmer from 60,000 pages per deploy to 4,400.</aside>

Every storefront page is [rendered twice](/blog/rendering-the-storefront-twice): once on the server, so crawlers and slow phones get real HTML, and once more in the browser. Varnish keeps the server's version for days, so most visitors never wait for it. The ones who do are the first visitor to a page after a deploy, the long tail of rarely viewed products, and every search engine walking the catalogue.

Those visitors waited about a second. On the busiest page route, the one behind product and family URLs, there were 508,000 cache misses in three days: an average of 337 ms but a p95 of 1.1 s. The database work behind each of them, about 46 queries, took around 50 ms. Almost all of the rest was a single call: PHP posting the page to the local render process and waiting for the HTML.

In July we profiled that second and blamed the payload: two hundred product cards, 418 KB of props, mostly image URLs. That was a reasonable story, and shrinking the payload helped a little. It wasn't the main cause.

## Three clues that didn't fit

The render calls are recorded per request in our own telemetry, and three numbers in it didn't fit a payload story.

**The same page cost four times more on one server.** Both web servers render the same pages from the same bundle. Over a day, the main one averaged 1,004 ms per render and the second one 243 ms, for payloads of the same size (98 KB in, 245 KB out on average).

**Size barely mattered.** Grouped by payload size, a 40 KB page took 630 ms and a 600 KB page 1,250 ms. Fifteen times the data cost twice the time, so most of the cost was a fixed amount per render, whatever the page.

**It got worse with uptime.** Averaged per hour, the main server's renders went from 1,043 ms to 1,328 ms over twelve hours of normal traffic, and reset after every deploy that restarted the process. Each of its 32 workers was holding about 1.1 GB after 41 hours, roughly 35 GB in all, with part of it in swap.

A cost that grows with uptime and resets on restart isn't load. It's a leak. And because deploys restart the render process several times a day, the leak never got far enough to look like an outage. It just made every miss a little slower each hour, and the next deploy reset it.

## Replaying a real page

The page object PHP sends to the render process is also printed into every page as the <code>data-page</code> attribute, so any live page can be replayed. We took one product page, built the render bundle into a scratch folder, started one worker, and posted the same page to it thousands of times.

Each render took 25 ms, so the render itself was never the problem. But the worker's memory grew by about 360 KB per render and passed 2.8 GB after 7,000 renders, with no sign of levelling off. Node grew too, so it wasn't the runtime.

Two heap snapshots, one after 50 renders and one after 550, showed what was piling up. The second held 39,000 more <code>ReactiveEffect</code> objects and the same number of dependency links, about 78 per render. Each link pointed at a dependency keyed by a string, and the keys were "Download File", "Origin Country", "Login or Register for Wholesale Prices" and "Product Specification": the translation keys used on a product page.

## A helper meant for components

Our storefront translates text through a small helper, <code>ctrans()</code>, which falls back to the English text when a translation is missing. Inside, it called <code>trans()</code> from the i18n library, twice per string. The library's <code>trans()</code> is written for a component in a browser. It calls <code>wTrans()</code>, which sets up a <code>watchEffect</code> to keep the message current and returns a <code>computed</code> for it, so a translated label updates when the language file finishes loading.

In a browser that's harmless: the component owns the effects and stops them when it unmounts. On the server, three things combined:

1. The calls come from templates, not component setup, so the effects have no owner and nothing ever stops them.
2. The library's plugin defaults to one **shared** instance per process, so every effect subscribes to that one long-lived message store and is never garbage-collected.
3. Every render installs the plugin again, which loads the request's language into that shared store. If the previous page was in Czech and this one is in English, almost every message changes, and every effect ever created for those keys runs again.

So each render left about 78 effects behind, and each change of language re-ran all of them. The cost per render grew with the number of pages drawn since the last restart and with how often languages alternated. The main server draws four times more pages than the second, across all of our storefront languages, which explains the gap between them.

It also explains why our July benchmark didn't catch it. Replaying one page in one language, the leaked effects never re-run: memory grows but time stays flat. Alternating an English and a Czech page in the same benchmark reproduced the production curve: 28 ms, then 47, 71 and 100 ms by render 1,500.

## Twenty lines

The fix doesn't use the library's watcher. The helper reads the message straight from the shared store, applies the replacements, and for plurals calls the library's own pluraliser. In the browser nothing is lost: the component's render function reads the message while it renders, so Vue still tracks it, and a language that finishes loading late still re-renders the label.

The new test counts how many leaked effects run again when another language loads. The old helper fails it with 413 re-runs; the new one runs none. Replayed with alternating languages for 3,000 renders, the new bundle stays at 22–24 ms with memory flat, and its HTML is byte-identical to the old bundle's.

## What else we found

**Production ran Vue's development build.** The render process starts under a process supervisor that never set <code>NODE_ENV</code>, so it ran Vue's development build: the render log held thousands of <code>[Vue warn]</code> lines, which the production build never prints. Setting it saves about a tenth of the render time.

**32 workers was too many.** Each server ran one render worker per CPU core. Fleet-wide there are about 1.4 renders a second, and at 36 ms each that needs a fraction of one core. Eight workers per server is plenty, and the main server's render memory went from about 35 GB to about 2 GB.

**Pages drawn without the server were kept for ten days.** When the render process is restarting, which happens for a couple of seconds on most deploys, Inertia falls back to letting the browser draw the page, and the response is still a 200. [Varnish](/blog/varnish-in-front-of-a-storefront-that-knows-who-you-are) caches every 200 page for ten days, so a page that happened to miss the cache in those seconds stayed as an empty shell for crawlers for ten days. Now the render gateway marks those responses, a middleware sends <code>X-Aiku-Ssr-Fallback: 1</code> and <code>Cache-Control: private, no-store</code>, and one rule in the VCL serves them without storing them.

## Before and after

Two hours after the deploy, on the server that does most of the rendering:

| | Before | After |
|---|---|---|
| Render, median | 1.2–1.5 s | 36 ms |
| Render, p95 | 1.6–2.2 s | 60–100 ms |
| Render on the second server, median | 290 ms | 38 ms |
| Render workers per server | 32 | 8 |
| Render memory, main server | about 35 GB | about 2.3 GB |
| Over the following two hours | climbing | flat |

The last row is the one that matters. A restart always made renders fast for a while. The leak showed in what happened next, and this time nothing happened.

## The warmer, again

We have [built the cache warmer three times](/blog/warming-the-cache-three-times), and the version we kept pre-built the pages behind 90% of the last thirty days' traffic after every deploy that changed the storefront: about 60,000 pages across the group, each costing a second of rendering. With a miss now costing about 36 ms of rendering and 50 ms of data, most of that work wasn't worth it, so the warmer now covers half the traffic: 4,400 pages, homepages and the busiest categories and products. The rest are built when someone first asks for them, a little over a tenth of a second later than a cache hit.

## What we'd tell ourselves in July

- **A cost that grows with uptime is a leak.** If restarts make something fast and time makes it slow, profiling a single request won't find it. Plot it by hour since restart.
- **Replay with production's variety.** One page in one language was the one benchmark that couldn't show this bug. Most real traffic switches languages between requests.
- **Reactive helpers aren't free on the server.** Anything that creates a <code>watch</code>, a <code>watchEffect</code> or a <code>computed</code> outside component setup will outlive the request on the server. Translation, formatting and feature-flag helpers are the usual suspects, because they're called from templates thousands of times per page.
- **Check your instruments.** Our telemetry client showed every timestamp eight hours early, and for a few minutes we believed the monitoring had stopped recording the night before. It hadn't, and the clients now read times as UTC.

<aside class="technical"><strong>Technical box</strong>
<ul>
<li>The helper: <a href="https://github.com/Inikoo-Ltd/aiku/blob/main/resources/js/Composables/useTrans.ts">resources/js/Composables/useTrans.ts</a>, which looks up <code>I18n.getSharedInstance().findTranslation()</code> and applies <code>makeReplacements()</code> and <code>choose()</code> instead of <code>trans()</code>/<code>transChoice()</code>. The regression test, <a href="https://github.com/Inikoo-Ltd/aiku/blob/main/tests/JavaScript/useTrans.test.ts">tests/JavaScript/useTrans.test.ts</a>, counts lookups re-run after another language loads.</li>
<li>Render entry: <a href="https://github.com/Inikoo-Ltd/aiku/blob/main/resources/js/ssr-iris.js">resources/js/ssr-iris.js</a>, with <code>INERTIA_SSR_WORKERS</code> setting the worker count. Process config: <a href="https://github.com/Inikoo-Ltd/aiku/blob/main/devops/supervisor/aiku-inertia-ssr.conf">devops/supervisor/aiku-inertia-ssr.conf</a>, with <code>NODE_ENV=production</code>.</li>
<li>Fallback pages: <a href="https://github.com/Inikoo-Ltd/aiku/blob/main/app/Services/ReportingSsrGateway.php">ReportingSsrGateway</a> flags the request, <a href="https://github.com/Inikoo-Ltd/aiku/blob/main/app/Http/Middleware/PreventCachingSsrFallback.php">PreventCachingSsrFallback</a> sets the headers, and the <code>X-Aiku-Ssr-Fallback</code> rule in <a href="https://github.com/Inikoo-Ltd/aiku/blob/main/devops/varnish/default.vcl">devops/varnish/default.vcl</a> serves them without storing them.</li>
<li>Warmer coverage: <code>TRAFFIC_COVERAGE</code> in <a href="https://github.com/Inikoo-Ltd/aiku/blob/main/app/Actions/Web/Crawl/CrawlWebsite.php">CrawlWebsite</a>.</li>
<li>The replay recipe: pull <code>data-page</code> from a live page, POST it to the render process's <code>/render</code> in a loop with one worker, and record time and memory per batch. For a leak, run Node with <code>--heapsnapshot-signal=SIGUSR2</code>, take two snapshots a few hundred renders apart, diff the object counts by constructor, then follow the leaked objects' dependency keys.</li>
</ul></aside>

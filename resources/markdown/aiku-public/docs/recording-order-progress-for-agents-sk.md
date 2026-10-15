---
title: Zaznamenávanie postupu objednávky ako agent
summary: Pre agentov — dátumy, ktoré zaznamenávate na každej objednávke dodávateľovi (zaplatená záloha, začatie výroby, dokončená výroba, navrhovaný dátum pripravenosti), kedy a kde ich zaznamenať, čo namiesto toho zaznamenáva nakupujúca spoločnosť, a prečo sa chýbajúci dátum zobrazuje ako meškanie.
date: 2026-10-15
source_date: 2026-10-15
tags: procurement, agents, supply-chain
category: procurement
series: PO journey
order: 2
---

<aside class="tldr">
Pre koordinačných agentov. Nakupujúca spoločnosť sleduje každú objednávku v siedmich fázach: odoslaná, záloha zaplatená, vo výrobe, pripravené a kontrola kvality prešla, expedované, dorazilo a preclené, prijaté. Sama vidí, kedy je tovar expedovaný a kedy dorazí, ale nevidí, čo sa deje u dodávateľa. Túto časť dopĺňate vy: na každej objednávke dodávateľovi zaznamenávate, kedy bola zaplatená záloha, kedy sa začala výroba, kedy bola výroba dokončená, a dátum, kedy očakávate, že bude tovar pripravený. Zaznamenajte každý z nich v ten deň, keď sa to stane. Strana nákupcu je vysvetlená v <a href="/docs/reading-the-po-journey-board-sk">čítaní tabule PO journey</a>.
</aside>

## Prečo záležia vaše dátumy

Každá objednávka, ktorú cez vás nakupujúca spoločnosť zadá, zobrazuje svoje fázy v hornej časti stránky objednávky a znova ako riadok na tabuli <b>PO journey</b>. Každá fáza ukazuje svoj dátum a kto ho zaznamenal. Keď fáza prekročí svoj cieľ, na tabuli sa zmení na červenú a zobrazí, o koľko dní mešká.

Bez vašich dátumov nakupujúca spoločnosť na vašej strane vidí len dve veci: deň, keď vám bola objednávka odoslaná, a deň, keď tovar odišiel. Všetko medzi tým je neviditeľné a meškanie vyzerá rovnako bez ohľadu na to, či nastalo u dodávateľa, pri kontrole kvality alebo pri odovzdaní. S vašimi dátumami fázy presne ukážu, kde sa objednávka nachádza, a meškajúci dodávateľ je viditeľný celé týždne predtým, než mal tovar odísť.

Len čo na objednávke zaznamenáte prvý dátum, ďalšie sa očakávajú. Ak cieľ fázy prejde a vy ju nezaznamenáte, táto fáza sa zobrazí ako meškajúca, aj keď bola práca hotová. Chýbajúci dátum vyzerá presne ako meškanie.

## Dátumy, ktoré zaznamenávate

| Dátum | Kedy ho zaznamenať | Kde |
|---|---|---|
| Deposit paid (zaplatená záloha) | Keď dodávateľ dostane zálohu. | Tlačidlo <b>Deposit paid</b> na fázach v hornej časti objednávky. Výšku zálohy zaznamenajte na stránke <b>Edit</b> objednávky. |
| Production started (začatie výroby) | Keď dodávateľ začne tovar vyrábať. | Tlačidlo <b>Production started</b> na fázach v hornej časti objednávky. |
| Production done (dokončená výroba) | Keď je tovar u dodávateľa hotový. Hotový znamená celá objednávka, nie prvá dávka. | Sekcia <b>Production</b> na stránke <b>Edit</b> objednávky. |
| Proposed ready date (navrhovaný dátum pripravenosti) | Keď viete, kedy bude tovar pripravený na odovzdanie. Dátum, ktorému veríte. Nákupca ho schvaľuje. | Sekcia <b>Clean handover</b> na stránke <b>Edit</b> objednávky. |

Fázy idú v poradí. Každý dátum zaznamenajte v deň, keď nastane. Dátum starší ako jeden deň, fáza zaznamenaná pred predchádzajúcou fázou alebo vymazanie dátumu si od vás vyžiada dôvod. Dôvod sa uchová v histórii objednávky spolu s vaším menom, aby nákupca videl, prečo.

## Čo zaznamenáva nakupujúca spoločnosť

Niektoré dátumy nemôžete zadať vy — je to zámer:

- **Sample approved (schválená vzorka).** Vzorku schvaľuje nákupca. Len pri nových produktoch; opakované objednávky vzorku nemajú.
- **Schválený dátum pripravenosti (Approved ready date).** Nákupca schváli váš navrhovaný dátum. Odvtedy je uzamknutý a určuje termíny: kontrola kvality má termín v ten deň a <b>Clean handover</b> do siedmich dní od neho.
- **Kontrola kvality (QC passed)** a <b>Clean handover</b>. Toto potvrdzuje nákupca, zvyčajne na základe vášho dôkazu o kvalite a potvrdenia prepravného agenta o prevzatí.
- **Kompletný compliance balík.**

Toto sú fakty, z ktorých sa počíta vaše <a href="/docs/your-clean-handover-score-sk">Clean Handover Score</a>. Skóre, ktoré by ste si mohli upraviť sami, by nemalo cenu — ani pre vás.

## Dobré návyky

- **Zaznamenajte v ten istý deň.** Päť sekúnd v ten deň vám neskôr ušetrí spor o červenej fáze aj písanie dôvodu.
- **Navrhujte dátumy pripravenosti, ktorým veríte.** Realistický dátum, ktorý dodržíte, je lepší než optimistický dátum, ktorý zmeškáte.
- **Informujte nákupcu včas**, keď sa dátum posunie. Nový schválený dátum dohodnutý včas udrží objednávku v termíne; posunutý dátum, na ktorom sa nikto nedohodol, sa zobrazí ako meškanie.

<aside class="wayfinder">
<b>Kde kliknúť v aiku</b><br>
Prihláste sa → <b>Purchase Orders</b> → otvorte objednávku. Každá objednávka je pre jedného z vašich dodávateľov. Sedem fáz je v hornej časti stránky: použite tam tlačidlá <b>Deposit paid</b> a <b>Production started</b>. Potom otvorte <b>Edit</b>: <b>Deposit amount</b> je v sekcii <b>Payments</b>, <b>Production done</b> v sekcii <b>Production</b> a <b>Proposed ready date</b> v sekcii <b>Clean handover</b>.
</aside>

<aside class="wayfinder">
<b>Aké práva potrebujete</b><br>
Prihlásenie pre vašu agentskú organizáciu. Môžete označovať len svoje vlastné fázy na svojich vlastných objednávkach: zaplatenú zálohu a začatie výroby, a na stránke Edit dokončenú výrobu, navrhovaný dátum pripravenosti a výšku zálohy. Schválenie vzorky, schválený dátum pripravenosti, QC, clean handover a compliance pre vás otvorené nie sú, pretože ich zaznamenáva nakupujúca spoločnosť.
</aside>

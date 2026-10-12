---
title: Plánovanie výrobného dňa
summary: Pre výrobného manažéra a vedúceho dielne - zaradenie úloh do denného plánu bez uvedenia mena, pridelenie každého kroku operátorovi a čo sa stane s prácou, ktorá sa v daný deň nedokončila.
date: 2026-10-12
source_date: 2026-10-12
tags: production, planning, floor
category: production
series: Ordering from partners
order: 13
---

<aside class="tldr">
Dvaja ľudia, dve úlohy. <b>Manažér</b> rozhoduje, čo sa v ktorý deň vyrába: na tabuli <b>To produce</b> (Na výrobu) vyberie karty a stlačí <b>Plan for this day</b> (Naplánovať na tento deň), bez uvedenia mena. <b>Vedúci dielne</b> otvorí <b>Operations → Day plan</b> (Prevádzka → Denný plán) a každý krok každej naplánovanej úlohy pridelí operátorovi. Každý operátor potom vidí svoje kroky na obrazovke dielne. Všetko, čo sa v daný deň nedokončilo, zostáva v pláne oranžovou farbou, kým to niekto nepotvrdí na nový deň.
</aside>

## Manažér plánuje deň

Na **Factory → To produce** (Továreň → Na výrobu) tabuľa funguje ako doteraz: karty idú z **Backlog** (Nevybavené) do **Preparing** (V príprave), kde sa nastaví množstvo, a z **Preparing** do **Assigned** (Pridelené), kde sa vytvorí pracovný príkaz.

Keď sa karta pustí na **Assigned**, malé okno s otázkou *Kto to vyrobí?* teraz začína dátumom a tlačidlom:

1. Vyberte deň. Predvolene je to dnes.
2. Stlačte **Plan for this day**.

Pracovný príkaz sa vytvorí na daný deň, bez uvedenia mena. Každá karta sa stane samostatným pracovným príkazom, takže každý sa dá zoradiť a posunúť samostatne. Viac kariet vybraných a pustených naraz sa naplánuje spolu, každá ako jeden pracovný príkaz.

Výber mena zo zoznamu nižšie funguje tak ako vždy: celá úloha ide tej osobe a nie je v pláne žiadneho dňa.

Do plánu sa dá zaradiť aj úloha, ktorá už má uvedené meno: stlačte ceruzku vedľa mena na jej karte a potom **Plan for this day**. Meno sa z úlohy odoberie a jej kroky pridelí vedúci dielne.

## Vedúci dielne prideľuje kroky

**Factory → Operations → Day plan** (Továreň → Prevádzka → Denný plán) zobrazuje úlohy naplánované na daný deň, jeden riadok na výrobok v úlohe.

| Stĺpec | Význam |
| --- | --- |
| Order | poradie úlohy v dni - zadaním čísla ju posuniete |
| Job | pracovný príkaz a jeho stav |
| Product | čo sa vyrába |
| Qty | koľko kusov |
| Steps and operators | každý krok receptúry: doteraz vyrobené / požadované, a komu je pridelený |
| Planned | deň, na ktorý bola úloha naplánovaná |

Každý krok má vlastný zoznam mien. Vyberte meno a krok sa objaví na obrazovke dielne tejto osoby, a na nikoho inom. Krok s oranžovým okrajom ešte nikoho nemá; lišta hore ich počíta, *3 steps without an operator* (3 kroky bez operátora), takže sa nič omylom nevynechá.

Ak chcete počas dňa zmeniť, kto krok robí, vyberte iné meno. Ak chcete krok niekomu odobrať, stlačte malý krížik v zozname. Ak už osoba začala, jej odvedená práca zostáva zaznamenaná pod jej menom: zoznam rozhoduje len o tom, kto krok vidí odteraz.

Dátum hore posunie plán na iný deň. Zajtrajší plán sa dá rozdeliť už dnes.

## Sledovanie dňa

Rovnaká stránka ukazuje, ako deň prebieha, a obnovuje sa sama:

- lišta hore: koľko úloh, *12 of 18 steps done* (12 z 18 krokov hotových), kroky bez operátora, úlohy nedokončené v svoj deň;
- vedľa každého kroku: sivý kruh (nezačatý), oranžový kruhový ukazovateľ (začatý) alebo zelená fajka (hotový), s počtom doteraz vyrobených kusov;
- *working now* (pracuje sa) a meno, keď má niekto krok otvorený na dielni.

Ľudia, ktorí stránku vidia, ale dielňu neriadia, vidia mená, nie zoznamy.

## Keď vedúci dielne chýba

Manažér otvorí tú istú stránku a kroky pridelí sám. Plánovanie aj prideľovanie krokov vyžadujú rovnakú pozíciu.

## Čo sa nedokončilo

Naplánovaná úloha s otvorenými krokmi po svojom dni nezmizne. Od nasledujúceho dňa sa zobrazuje v Day plan so starým dátumom oranžovou farbou a lišta ju počíta ako *not finished on its day* (nedokončená v svoj deň). Pri každej vyberte:

- **Confirm for** (Potvrdiť na) deň, ktorý práve máte otvorený - úloha sa presunie do plánu tohto dňa, na koniec poradia;
- **Take off the plan** (Vyradiť z plánu) - úloha zostane ako pracovný príkaz, ale nie je v pláne žiadneho dňa.

Kým sa nestlačí jedno z týchto dvoch, úloha vám zostáva na očiach každý deň. Jej kroky zostávajú aj na obrazovkách dielne operátorov, ktorým boli pridelené, takže kto včera zmes pozastavil, môže pokračovať ráno.

Keď operátor uzavrie úlohu s menším počtom a zvyšok presunie do novej úlohy (pozrite [Práca s obrazovkou dielne](/docs/working-the-floor-screen-sk)), nová úloha si ponechá naplánovaný deň, miesto v poradí a operátora každého kroku.

<aside class="wayfinder"><strong>Kde kliknúť v aiku</strong>
<ul>
<li><b>Naplánovať úlohu na deň:</b> <b>Factory</b> → <b>To produce</b> → pustite kartu na <b>Assigned</b> → vyberte deň → <b>Plan for this day</b>.</li>
<li><b>Pridelenie kroku operátorovi:</b> <b>Factory</b> → <b>Operations</b> → <b>Day plan</b> → zoznam vedľa kroku.</li>
<li><b>Zmena poradia dňa:</b> zadajte číslo do <b>Order</b>.</li>
<li><b>Včerajšia nedokončená úloha:</b> <b>Confirm for</b> deň, alebo <b>Take off the plan</b>.</li>
<li><b>Z tabule do plánu:</b> odkaz <b>Day plan</b> nad tabuľou.</li>
</ul>
</aside>

<aside class="wayfinder"><strong>Aké oprávnenia potrebujete</strong>
<ul>
<li>Pozície sa nastavujú v karte zamestnanca v Human Resources a nesú so sebou oprávnenia.</li>
<li>Plánovanie dňa, prideľovanie krokov, zmena poradia: <b>Floor supervisor</b> pre danú továreň.</li>
<li>Pozeranie na Day plan bez možnosti zmeny: <b>Foreman</b>.</li>
<li>Práca na kroku, ktorý vám bol pridelený: <b>Operative</b>, na obrazovke dielne.</li>
</ul>
</aside>

---
title: Plánování výrobního dne
summary: Pro manažera výroby a vedoucí výroby - zařazení úkolů do plánu dne bez uvedení jména, přidělení každého kroku dělníkovi a co se stane s prací, která se ten den nedokončila.
date: 2026-10-12
source_date: 2026-10-12
tags: production, planning, floor
category: production
series: Ordering from partners
order: 13
---

<aside class="tldr">
Dva lidé, dvě úlohy. <b>Manažer</b> rozhoduje, co se ten den vyrobí: na nástěnce <b>To produce</b> (K výrobě) vybere karty a stiskne <b>Plan for this day</b> (Naplánovat na tento den), aniž by uvedl jméno. <b>Vedoucí výroby</b> otevře <b>Operations → Day plan</b> (Provoz → Denní plán) a každý krok každého naplánovaného úkolu přidělí dělníkovi. Každý dělník pak vidí své kroky na obrazovce dílny. Co se ten den nedokončí, zůstává v plánu oranžově, dokud někdo nepotvrdí nový den.
</aside>

## Manažer plánuje den

Na **Factory → To produce** (Továrna → K výrobě) nástěnka funguje jako dřív: karty putují z **Backlog** (Nevyřízené) do **Preparing** (Příprava), kde se nastaví množství, a z **Preparing** do **Assigned** (Přiděleno), kde se vytvoří výrobní příkaz.

Když se karta pustí na **Assigned**, malé okno s otázkou *Who makes it?* (Kdo to vyrobí?) teď začíná datem a tlačítkem:

1. Vyberte den. Otevře se na dnešku.
2. Stiskněte **Plan for this day**.

Pro ten den se vytvoří výrobní příkaz, na kterém není nikdo uveden. Každá karta se stane samostatným výrobním příkazem, takže každý lze řadit a posouvat zvlášť. Několik karet vybraných a puštěných společně se plánuje společně, každá jako jeden výrobní příkaz.

Výběr jména ze seznamu níže funguje stále jako dřív: celý úkol připadne té osobě a není v plánu žádného dne.

Do plánu lze zařadit i úkol, na kterém už někdo uveden je: stiskněte tužku vedle jména na jeho kartě a pak **Plan for this day**. Jméno se z úkolu odebere a jeho kroky přidělí vedoucí výroby.

## Vedoucí výroby přiděluje kroky

**Factory → Operations → Day plan** vypíše úkoly naplánované na daný den, jeden řádek na výrobek v úkolu.

| Sloupec | Význam |
| --- | --- |
| Order (Pořadí) | místo úkolu v rámci dne - zadáním čísla ho posunete |
| Job (Úkol) | výrobní příkaz a jeho stav |
| Product (Výrobek) | co se vyrábí |
| Qty (Množství) | kolik |
| Steps and operators (Kroky a dělníci) | každý krok receptu: vyrobeno zatím / požadováno a komu je přidělen |
| Planned (Naplánováno) | den, na který bylo naplánováno |

Každý krok má vlastní seznam jmen. Vyberte jedno a krok se objeví na obrazovce dílny té osoby a nikoho jiného. Krok s oranžovým okrajem zatím nikoho nemá; lišta nahoře je počítá, *3 steps without an operator* (3 kroky bez dělníka), aby se omylem nic nevynechalo.

Chcete-li během dne změnit, kdo krok dělá, vyberte jiné jméno. Chcete-li krok někomu odebrat, stiskněte malý křížek v seznamu. Pokud už dotyčný začal, práce, kterou udělal, zůstává zapsaná pod jeho jménem: seznam rozhoduje jen o tom, kdo krok uvidí od teď.

Datum nahoře přepíná na jiný den. Plán na zítřek lze rozdělit už dnes.

## Sledování dne

Stejná stránka ukazuje, jak den probíhá, a obnovuje se sama:

- lišta nahoře: kolik úkolů, *12 of 18 steps done* (12 z 18 kroků hotovo), kroky bez dělníka, úkoly nedokončené v daný den;
- u každého kroku: šedý kruh (nezačato), oranžový kolečkový ukazatel (začato) nebo zelená fajfka (hotovo) s počtem dosud vyrobených kusů;
- *working now* (právě pracuje) a jméno, když má někdo krok otevřený na dílně.

Lidé, kteří stránku vidí, ale dílnu neřídí, vidí jména, ne seznamy.

## Když vedoucí výroby chybí

Manažer otevře stejnou stránku a kroky přidělí sám. Plánování i přidělování kroků vyžaduje stejnou pozici.

## Co se nedokončilo

Naplánovaný úkol s kroky, které zůstaly po jeho dni otevřené, se neztratí. Od dalšího dne se v Day plan zobrazuje se starým datem oranžově a lišta ho počítá jako *not finished on its day* (nedokončeno v daný den). U každého si vyberte:

- **Confirm for** (Potvrdit na) den, který právě prohlížíte - úkol se přesune do plánu toho dne, na konec pořadí;
- **Take off the plan** (Vyřadit z plánu) - úkol zůstane jako výrobní příkaz, ale není v plánu žádného dne.

Dokud se nestiskne jedno z obojího, úkol vám zůstává před očima každý den. Jeho kroky zůstávají i na obrazovkách dílny dělníků, kterým byly přiděleny, takže kdo včera pozastavil směs, může dnes ráno pokračovat.

Když dělník uzavře úkol s menším množstvím a zbytek přesune do nového úkolu (viz [Práce s obrazovkou dílny](/docs/working-the-floor-screen-cs)), nový úkol si ponechá naplánovaný den, místo v pořadí a dělníka u každého kroku.

<aside class="wayfinder"><strong>Kam kliknout v aiku</strong>
<ul>
<li><b>Naplánovat úkol na den:</b> <b>Factory</b> → <b>To produce</b> → pusťte kartu na <b>Assigned</b> → vyberte den → <b>Plan for this day</b>.</li>
<li><b>Přidělit krok dělníkovi:</b> <b>Factory</b> → <b>Operations</b> → <b>Day plan</b> → seznam vedle kroku.</li>
<li><b>Změnit pořadí dne:</b> zadejte číslo do <b>Order</b>.</li>
<li><b>Včerejší nedokončený úkol:</b> <b>Confirm for</b> den, nebo <b>Take off the plan</b>.</li>
<li><b>Z nástěnky do plánu:</b> odkaz <b>Day plan</b> nad nástěnkou.</li>
</ul>
</aside>

<aside class="wayfinder"><strong>Jaká oprávnění potřebujete</strong>
<ul>
<li>Pozice se nastavují na kartě zaměstnance v sekci Human Resources a nesou s sebou příslušná práva.</li>
<li>Plánování dne, přidělování kroků, změna pořadí: <b>Floor supervisor</b> pro danou továrnu.</li>
<li>Zobrazení Day plan bez možnosti změn: <b>Foreman</b>.</li>
<li>Práce na kroku, který vám byl přidělen: <b>Operative</b>, na obrazovce dílny.</li>
</ul>
</aside>

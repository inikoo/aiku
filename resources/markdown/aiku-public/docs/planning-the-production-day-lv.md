---
title: Ražošanas dienas plānošana
summary: Ražošanas vadītājam un maiņas vadītājam - darbu ievietošana dienas plānā, nenorādot nevienu cilvēku, katra soļa nodošana strādniekam un kas notiek ar darbu, kas savā dienā nav pabeigts.
date: 2026-10-12
source_date: 2026-10-12
tags: production, planning, floor
category: production
series: Ordering from partners
order: 13
---

<aside class="tldr">
Divi cilvēki, divi uzdevumi. <b>Ražošanas vadītājs</b> izlemj, ko izgatavo konkrētā dienā: dēlī <b>To produce</b> (Ražojamie darbi) viņa izvēlas kartītes un nospiež <b>Plan for this day</b> (Ieplānot šai dienai), nenorādot nevienu cilvēku. <b>Maiņas vadītājs</b> atver <b>Operations → Day plan</b> (Darbības → Dienas plāns) un katru ieplānotā darba soli nodod kādam strādniekam. Katrs strādnieks pēc tam savus soļus redz ražotnes ekrānā. Viss, kas savā dienā nav pabeigts, paliek plānā oranžā krāsā, līdz kāds to apstiprina jaunai dienai.
</aside>

## Vadītājs plāno dienu

Sadaļā **Factory → To produce** (Ražotne → Ražojamie darbi) dēlis darbojas kā iepriekš: kartītes iet no **Backlog** (Rindā) uz **Preparing** (Sagatavošanā), kur norāda daudzumu, un no **Preparing** uz **Assigned** (Piešķirts), kur tiek izveidots darba uzdevums.

Kad kartīti nomet uz **Assigned**, nelielais logs ar jautājumu *Who makes it?* (Kas to izgatavo?) tagad sākas ar datumu un pogu:

1. Izvēlies dienu. Logs atveras ar šodienu.
2. Nospied **Plan for this day**.

Tiek izveidots darba uzdevums šai dienai, un uz tā nav norādīts neviens cilvēks. Katra kartīte kļūst par savu darba uzdevumu, tāpēc katru var sakārtot un pārvietot atsevišķi. Ja vairākas kartītes atlasa un nomet kopā, tās tiek ieplānotas kopā, katra ar savu darba uzdevumu.

Vārda izvēle no saraksta zemāk darbojas tāpat kā vienmēr: viss darbs nonāk pie šī cilvēka, un tas nav nevienas dienas plānā.

Arī darbu, kam jau ir norādīts cilvēks, var ielikt plānā: nospied zīmuli blakus vārdam uz tā kartītes un tad **Plan for this day**. Vārds no darba tiek noņemts, un tā soļus izdala maiņas vadītājs.

## Maiņas vadītājs izdala soļus

**Factory → Operations → Day plan** uzskaita dienai ieplānotos darbus, katram produktam darbā viena rinda.

| Kolonna | Nozīme |
| --- | --- |
| Order | darba vieta dienā - ieraksti skaitli, lai to pārvietotu |
| Job | darba uzdevums un tā stāvoklis |
| Product | kas tiek izgatavots |
| Qty | cik daudz |
| Steps and operators | katrs receptes solis: līdz šim izgatavots / prasīts, un kam tas nodots |
| Planned | diena, kurai tas ieplānots |

Katram solim ir savs vārdu saraksts. Izvēlies vienu, un šis solis parādās šī cilvēka ražotnes ekrānā un nevienam citam. Solis ar oranžu apmali vēl nav nevienam nodots; josla augšā tos saskaita, *3 steps without an operator* (3 soļi bez strādnieka), lai nekas netiktu aizmirsts.

Lai mainītu, kas veic soli dienas laikā, izvēlies citu vārdu. Lai soli atņemtu cilvēkam, nospied mazo krustiņu sarakstā. Ja cilvēks jau bija sācis, viņa padarītais darbs paliek reģistrēts zem viņa vārda: saraksts nosaka tikai to, kam solis būs redzams turpmāk.

Datums augšā pārslēdz uz citu dienu. Rītdienas plānu var izdalīt jau šodien.

## Dienas gaitas uzraudzība

Tā pati lapa rāda, kā diena virzās, un atjaunojas pati:

- josla augšā: cik darbu, *12 of 18 steps done* (12 no 18 soļiem pabeigti), soļi bez strādnieka, darbi, kas savā dienā nav pabeigti;
- blakus katram solim: pelēks aplis (nav sākts), oranžs griežas aplis (sākts) vai zaļa ķeksīte (pabeigts) ar līdz šim izgatavoto skaitu;
- *working now* (strādā tagad) un vārds, kad kāds solis ir atvērts ražotnes ekrānā.

Cilvēki, kas lapu redz, bet nevada ražotni, redz vārdus, nevis sarakstus.

## Kad maiņas vadītāja nav

Ražošanas vadītājs atver to pašu lapu un soļus izdala pats. Plānošanai un soļu izdalīšanai vajadzīgs viens un tas pats amats.

## Kas netika pabeigts

Ieplānots darbs, kuram pēc savas dienas vēl ir atvērti soļi, netiek aizmirsts. No nākamās dienas tas parādās sadaļā Day plan ar savu veco datumu oranžā krāsā, un josla to saskaita kā *not finished on its day* (savā dienā nepabeigts). Katram no tiem izvēlies:

- **Confirm for** (Apstiprināt) dienu, kuru skati - darbs pāriet uz šīs dienas plānu, secības beigās;
- **Take off the plan** (Noņemt no plāna) - darbs paliek kā darba uzdevums, bet nav nevienas dienas plānā.

Kamēr nav nospiesta viena no abām, darbs katru dienu paliek tavā redzeslokā. Tā soļi paliek arī to strādnieku ražotnes ekrānos, kuriem tie nodoti, tāpēc tas, kurš vakar pārtrauca maisījumu, to var turpināt šorīt.

Kad strādnieks aizver darbu pie mazāka daudzuma un atlikumu pārceļ uz jaunu darbu (skaties [Darbs ar ražotnes ekrānu](/docs/working-the-floor-screen-lv)), jaunais darbs saglabā ieplānoto dienu, vietu secībā un katra soļa strādnieku.

<aside class="wayfinder"><strong>Kur klikšķināt aiku sistēmā</strong>
<ul>
<li><b>Ieplānot darbu dienai:</b> <b>Factory</b> → <b>To produce</b> → nomet kartīti uz <b>Assigned</b> → izvēlies dienu → <b>Plan for this day</b>.</li>
<li><b>Nodot soli strādniekam:</b> <b>Factory</b> → <b>Operations</b> → <b>Day plan</b> → saraksts blakus solim.</li>
<li><b>Mainīt dienas secību:</b> ieraksti skaitli laukā <b>Order</b>.</li>
<li><b>Vakardienas nepabeigtais darbs:</b> <b>Confirm for</b> dienu vai <b>Take off the plan</b>.</li>
<li><b>No dēļa uz plānu:</b> saite <b>Day plan</b> virs dēļa.</li>
</ul>
</aside>

<aside class="wayfinder"><strong>Nepieciešamās tiesības</strong>
<ul>
<li>Amati tiek piešķirti darbinieka kartītē sadaļā Human Resources un ar sevi nes tiesības.</li>
<li>Dienas plānošana, soļu izdalīšana, secības maiņa: <b>Floor supervisor</b> šai ražotnei.</li>
<li>Dienas plāna skatīšana bez izmaiņām: <b>Foreman</b>.</li>
<li>Savam vārdam nodota soļa izpilde: <b>Operative</b>, ražotnes ekrānā.</li>
</ul>
</aside>

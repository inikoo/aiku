---
title: Ražošanas dienas plānošana
summary: Ražošanas vadītājam un maiņas vadītājam - darbu ievietošana dienas plānā, nenorādot nevienu cilvēku, katra soļa nodošana strādniekam un kas notiek ar darbu, kas savā dienā nav pabeigts.
date: 2026-10-15
source_date: 2026-10-15
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

## Rīta materiālu pārbaude

Noliktava materiālus uz ražotni atved veselās kastēs, nevis precīzos dienas daudzumos. **Factory → Operations → Materials check** (Ražotne → Darbības → Materiālu pārbaude) uzskaita to, kas dienas plānam vēl vajadzīgs, lai ražotne pārbauda, kas ir uz vietas, un pārējo noliktavai pasūta vienā skaidrā sarakstā.

Katra rinda ir viens materiāls, saskaitīts pa visiem dienas darbiem, ar darbiem, kam tas vajadzīgs (*JOaroma-0141, JOaroma-0145*). Tiek skaitīti tikai vēl neizpildītie soļi: kad izgatavošana ir pabeigta un atlicis tikai iepakošana, rindā redzamas kastes un etiķetes, bet vairs ne pudeles. Maisījumi tiek gatavoti ražotnē, un sarakstā tos nav. Daudzumi nāk no receptēm, tāpēc darbam, kura receptē nav norādīts neviens materiāls, nekas nav vajadzīgs: oranžā josla virs saraksta nosauc šos darbus, lai receptes trūkums būtu redzams.

| Kolonna | Nozīme |
| --- | --- |
| Material | materiāls un noliktavas krājuma kods |
| For jobs | dienas darbi, kuriem tas vajadzīgs |
| Needed | cik daudz patērē vēl veicamie soļi |
| At the factory | cik šobrīd ir ražotnes atrašanās vietā un cik šodien jau pasūtīts |
| Check | atzīmē **Have enough** (Pietiek) vai ieraksti aptuveni, cik trūkst |

Kad ierakstīts trūkstošais skaits, rindā redzams, ko noliktava atvedīs, noapaļojot uz augšu līdz tās kastēm: trūkst 1 200, kastē 500 - *bring 3 boxes (1,500)* (atvest 3 kastes (1 500)). Sarkans *Warehouse short* (Noliktavā nepietiek) nozīmē, ka noliktavā tik daudz nav, un to uzzini uzreiz.

Nospied **Send to warehouse** (Nosūtīt noliktavai). Trūkstošās rindas aiziet noliktavai kā viens pieprasījums, un pieprasījums parādās virs saraksta, katrs materiāls ar atzīmi *To bring* (Jāatved), *Delivered* (Piegādāts) vai *Warehouse short*. Ja visu atzīmē kā pietiekošu un nosūta, tiek reģistrēts, ka pārbaude ir veikta. Otra pārbaude vēlāk dienā pievieno tikai to, kas vēl trūkst.

Pārbaudi veic vadītājs vai maiņas vadītājs, vai arī nodod to strādniekam ar **Checked by** (Pārbaudīja) augšā. Šis strādnieks tad ražotnes ekrānā redz atgādinājumu, kas atver pārbaudi.

## Noliktava atved materiālus

Noliktavā **Goods out → For production** (Preču izsniegšana → Ražošanai) uzskaita katru materiālu, ko ražotne pieprasījusi, plauktu apiešanas secībā, ar krājuma kodu, atvedamajām kastēm un ražotnes atrašanās vietu, uz kurieni tās dodas. Komplektētājs izvēlas plauktu, pārbauda daudzumu un nospiež **Delivered**. Ja noliktava to nevar atvest, **Warehouse short** paziņo ražotnei.

Piegādātais krājums no plaukta pāriet uz ražotnes atrašanās vietu. Tas paliek krājums: to skaita un redz kā agrāk, un klienta pasūtījums to tur joprojām var komplektēt, ja noliktavas plaukti ir tukši, vienmēr vispirms ņemot no plauktiem. Neizmantotos materiālus var pārvietot atpakaļ uz plauktu no ražotnes atrašanās vietas lapas, tāpat kā jebkuru citu pārvietošanu starp atrašanās vietām.

Kad pabeigts darbs tiek pieņemts krājumā, tā materiāli vispirms tiek norakstīti no ražotnes atrašanās vietas pēc receptes daudzumiem un tikai tad no citām vietām. Ražotnes atrašanās vietu laiku pa laikam saskaiti, lai izlabotu bojājumus un zudumus.

<aside class="wayfinder"><strong>Kur klikšķināt aiku sistēmā</strong>
<ul>
<li><b>Ieplānot darbu dienai:</b> <b>Factory</b> → <b>To produce</b> → nomet kartīti uz <b>Assigned</b> → izvēlies dienu → <b>Plan for this day</b>.</li>
<li><b>Nodot soli strādniekam:</b> <b>Factory</b> → <b>Operations</b> → <b>Day plan</b> → saraksts blakus solim.</li>
<li><b>Mainīt dienas secību:</b> ieraksti skaitli laukā <b>Order</b>.</li>
<li><b>Vakardienas nepabeigtais darbs:</b> <b>Confirm for</b> dienu vai <b>Take off the plan</b>.</li>
<li><b>No dēļa uz plānu:</b> saite <b>Day plan</b> virs dēļa.</li>
<li><b>Pārbaudīt dienas materiālus:</b> <b>Factory</b> → <b>Operations</b> → <b>Materials check</b> → <b>Have enough</b> vai trūkstošais skaits → <b>Send to warehouse</b>.</li>
<li><b>Nodot pārbaudi strādniekam:</b> <b>Materials check</b> → <b>Checked by</b>.</li>
<li><b>Atvest materiālus (noliktava):</b> <b>Goods out</b> → <b>For production</b> → <b>Delivered</b> vai <b>Warehouse short</b>.</li>
</ul>
</aside>

<aside class="wayfinder"><strong>Nepieciešamās tiesības</strong>
<ul>
<li>Amati tiek piešķirti darbinieka kartītē sadaļā Human Resources un ar sevi nes tiesības.</li>
<li>Dienas plānošana, soļu izdalīšana, secības maiņa: <b>Floor supervisor</b> šai ražotnei.</li>
<li>Dienas plāna skatīšana bez izmaiņām: <b>Foreman</b>.</li>
<li>Savam vārdam nodota soļa izpilde: <b>Operative</b>, ražotnes ekrānā.</li>
<li>Materiālu pārbaude: <b>Floor supervisor</b> vai strādnieks, kuram tā nodota.</li>
<li>Materiālu atvešana uz ražotni: noliktavas amats, kas strādā ar preču izsniegšanu.</li>
</ul>
</aside>

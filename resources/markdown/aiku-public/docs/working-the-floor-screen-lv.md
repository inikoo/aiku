---
title: Darbs ar ražotnes ekrānu
summary: Amatnieka rokasgrāmata - ekrāns Manu uzdevumi, darbu saraksts, kas atjaunojas pats, START un DONE, un ko spiest, ja izgatavoji mazāk, nekā prasīts.
date: 2026-10-12
source_date: 2026-10-12
tags: production, floor, artisan
category: production
series: Ordering from partners
order: 9
---

<aside class="tldr">
Tiem, kas ražo. <b>Factory</b> → <b>Jobs</b> (Ražotne → Darbi) ir visa tava diena vienā ekrānā: kreisajā pusē tev adresētie darbi, vidū tas, ar kuru strādā tagad. Spied <b>START</b> (Sākt), izgatavo, ieraksti, cik gatavs, spied <b>DONE</b> (Pabeigts). Ja izgatavoji mazāk, nekā prasīts, ekrāns uzdod vienu jautājumu: pauzēt un turpināt vēlāk, aizvērt darbu un pārcelt atlikumu uz jaunu, vai pabeigt to šeit. Nekas cits nav jāaizpilda.
</aside>

## Ekrāns

Lapa izskatās kā iesūtne.

**Kreisajā pusē, vienmēr redzams:** saraksts. Augšā divi skaitītāji - šodien izgatavotās vienības un šodien pabeigtie uzdevumi. Zem tiem **Your jobs** (Tavi darbi) - tev adresētais darbs: veseli darbi ar tavu vārdu un atsevišķi soļi, ko maiņas vadītājs tev nodevis dienas plānā (skaties [Ražošanas dienas plānošana](/docs/planning-the-production-day-lv)). Vienam darbam izgatavošana var būt pie viena cilvēka, bet iepakošana pie cita; katrs redz tikai savu soli. Ja tavs amats ļauj ņemt no kopīgā klāsta, seko sadaļa **Open jobs** (Brīvie darbi). Apakšā **Finished today** (Šodien pabeigtie) - tas, ko jau esi aizvēris.

**Labajā pusē:** tas, ko izvēlējies. Pirms sākšanas tā ir darba informācija ar lielu **START** pogu. Kad darbs sākts, tā ir darba kartīte ar pulksteni.

Sarakstu nevajag pārlādēt. Kad plānotājs tev kaut ko piešķir, kad kolēģis sāk kādu no brīvajiem darbiem, kad tu kaut ko aizver - saraksts mainās pats, uz visiem ekrāniem, kas rāda šo ražotni.

## Viena rinda sarakstā

Katra rinda ir viens uzdevums vienam darba uzdevumam:

| Kolonna | Nozīme |
| --- | --- |
| Code | ražojamais produkts - kods, piemēram, SKO-01 |
| Name | tā nosaukums |
| Task · job order | solis (Production, Labelling...) un darba uzdevuma numurs |
| 0/25 | izgatavots / prasīts |

Rinda ar zaļu ▶ un laiku ir tā, ar kuru strādā, un kad to sāki. Dzeltena piezīme zem rindas nozīmē, ka tā gaida maisījumu vai to jau atvēris kāds cits.

## Darba izpilde

1. Pieskaries rindai. Labajā pusē parādās informācija.
2. Spied **START** (Sākt). Sākas pulkstenis, un rindai parādās zaļš marķieris.
3. Izgatavo.
4. Ieraksti izgatavoto skaitu laukā **Quantity made** (Izgatavotais daudzums). Ja esi meistars vai augstāk, ir arī lauks **Rejected** (Brāķis) precēm, kas neizturēja pārbaudi.
5. Spied **DONE** (Pabeigts).

Izgatavoji visu, kas prasīts? Ar to viss. Uzdevums aizveras, darba uzdevums ir pabeigts, un noliktava saņem ziņu, ka ir kaut kas jāpieņem - skaties [Gatavās produkcijas pieņemšana noliktavā](/docs/putting-away-finished-production-lv).

## Ja izgatavoji mazāk, nekā prasīts

Ieraksti reālo skaitu un spied **DONE** (Pabeigts). Ievades rindas vietā parādās īss panelis: *19 gatavi · 6 vēl jāizgatavo* un četras pogas.

- **Pause** (Pauze) - tas pats maisījums, turpināsi vēlāk. Darbs paliek atvērts ar to pašu atsauci un to pašu partijas kodu, un skaitīšana turpinās no 19. Izmanto, ja diena beidzas pirms maisījuma.
- **Close at 19 and move remaining 6 to new job …** (Aizvērt pie 19 un pārcelt atlikušos 6 uz jaunu darbu) - darbs aizveras ar 19, un 6 nonāk jaunā darba uzdevumā ar nākamo burtu (0127 kļūst par 0127a) un savu partijas kodu. Izmanto, ja atlikums būs jauns maisījums. Ekrāns lūdz apstiprināt ar **Yes, close and move** (Jā, aizvērt un pārcelt), jo aizvērtu darbu vairs nevar turpināt.
- **Job finished** (Darbs pabeigts) - darbs aizveras ar 19, un ar to viss. Plānotājs redz darba uzdevumu, kas prasīja 25, bet saņēma 19.
- **Back** (Atpakaļ) - mainīt skaitli.

Darbs, kas pārcelts uz jaunu, savā kartītē rāda **Continued from 0127** (Turpinājums no 0127), bet darbs, no kura pārcelts, rāda **Continued in 0127a** (Turpināts darbā 0127a). Jaunais darbs paliek pie tiem pašiem cilvēkiem un tajā pašā dienas plānā.

## Ja izgatavoji vairāk, nekā prasīts

Ieraksti īsto skaitu un nospied **DONE**. Ekrāns brīdina *18 above target, a manager must authorise it* (18 virs mērķa, jāapstiprina vadītājam) un atver **Manager authorisation required for overproduction**.

- **Scan the badge** - vadītājs vai uzraugs pietur pie kameras savu personīgo QR kodu (to pašu, ar kuru reģistrē ierašanos). Tas tiek pieņemts, tiklīdz nolasīts. **Switch camera** pārslēdz starp priekšējo un aizmugurējo kameru.
- **Use manager PIN** - ja kamera to nenolasa, vadītājs tā vietā ievada savu darba laika PIN.

Apstiprināt var tikai tas, kurš vada šo ražošanas cehu, un nekad savam darbam. Pēc piecām nepareizām kartēm vai PIN logs 15 minūtes atsakās.

Pēc apstiprināšanas darbs pieaug līdz tam, kas patiešām izgatavots: noliktava novieto visu, ko neviens pasūtījums neprasīja, nonāk krājumā, un izejvielas tiek norakstītas par visu partiju. Tā paša darba nākamie soļi arī pieaug, lai nākamais cilvēks varētu pabeigt visu. Ražošanas pasūtījuma lapā redzams, kas izgatavoja pārpalikumu, kas to apstiprināja, kā un kad.

## Viena partija vairākām rindām

Dažreiz viena partija kalpo vairākiem darbiem. Piemēram, 100 HCS-48 klaipi un 100 sagrieztie SLHCS-48 klaipi ar to pašu smaržu tiek maisīti, lieti un formēti kopā kā 200 klaipi.

**Apvienošana (vadītāji).** Pieskaries vienai no rindām ceha ekrānā. Zem tās detaļām **Combine Production with other lines into one batch** parāda pārējās atvērtās rindas, kas gaida to pašu soli. Atzīmē tās, kas iet tajā pašā partijā, un nospied **Combine**. Rinda, kas jau ir apvienota, pabeigta vai pie kuras strādā, netiek piedāvāta.

**Darbs pie tās (amatnieki).** Rindas tagad redzamas kā viena rinda ar 🔗 un kodiem kopā, *HCS-48 + SLHCS-48*, un kopsummu, *0/200*. Detaļās katra rinda ir ar savu pasūtījumu. Nospied **START** vienreiz, izgatavo partiju, ieraksti kopējo izgatavoto daudzumu - 200 - un nospied **DONE** vienreiz.

**Ko Aiku ar to dara.** Kopsumma tiek sadalīta starp rindām pēc tā, cik katrai vēl bija jāizgatavo, veselās vienībās: 100 HCS-48, 100 SLHCS-48. Laiks tiek sadalīts tāpat. Katra rinda pēc tam turpina ar saviem nākamajiem soļiem - vienai iepakošana plēvē, otrai griešana un tad iepakošana. Pasūtījuma lapā solis redzams kā *One batch with* (viena partija ar) otru rindu.

**Samaksa un mērķi** tiek aprēķināti partijai kopumā: 200 klaipi stundās, cik tas aizņēma, pret soļa mērķi. Apvienošana samaksā tieši tik, cik atsevišķas partijas tādā pašā ātrumā. Ja rindām solim ir dažādi mērķi, partijas mērķis ir tas, kas aizņem tikpat stundu, cik atsevišķās partijas.

Izgatavot vairāk, nekā visas rindas kopā prasīja, var tikai ar vadītāja karti vai PIN, kā iepriekš; pārpalikums arī tiek sadalīts starp rindām. Vadītājs var rindas atkal atdalīt (**Separate**), kad pie tām neviens nestrādā. Tas, kas jau izgatavots kopā, paliek katrai rindai.

## Noderīgi zināt

- **Viens darbs vienlaikus.** Kamēr tev ir atvērts darbs, START pogas ir neaktīvas. Vispirms aizver to.
- **Tava samaksa par uzdevumu** seko skaitlim laukā Quantity made, kā aprakstīts [Ražotnes amatos](/docs/factory-positions-lv). Par brāķi nemaksā.
- **Open jobs** (Brīvie darbi) ir darbi un soļi bez norādīta amatnieka vai ar norādītu kādu citu. Ņemt tādu ir pieļaujami, ja tavs amats to atļauj - tad tas ir tavs, līdz aizvērts.

<aside class="wayfinder"><strong>Kur klikšķināt aiku sistēmā</strong>
<ul>
<li><b>Tavs ekrāns:</b> tava organizācija → <b>Factory</b> → <b>Jobs</b>.</li>
<li><b>Sākt:</b> pieskaries rindai → <b>START</b>.</li>
<li><b>Pabeigt:</b> ieraksti <b>Quantity made</b> → <b>DONE</b>.</li>
<li><b>Mazāk nekā prasīts:</b> <b>DONE</b> → <b>Pause</b>, <b>Close at … and move remaining …</b> vai <b>Job finished</b>.</li>
<li><b>Viena partija vairākām rindām:</b> pieskaries rindai → <b>Combine … with other lines into one batch</b> → atzīmē rindas → <b>Combine</b>. Lai atceltu: pieskaries apvienotajai rindai → <b>Separate</b>.</li>
</ul>
</aside>

<aside class="wayfinder"><strong>Nepieciešamās tiesības</strong>
<ul>
<li>Amati tiek piešķirti darbinieka kartītē sadaļā Human Resources un ar sevi nes tiesības.</li>
<li>Savu darbu redzēšanai, START un DONE: amats <b>Operative</b> šai ražotnei.</li>
<li>Brīvo darbu (<b>Open jobs</b>) redzēšanai un ņemšanai, brāķa reģistrēšanai: <b>Foreman</b>, <b>Mix preparer</b> vai <b>Floor supervisor</b>.</li>
<li>Rindu apvienošana un atdalīšana: vadītāji, kas vada ražošanas cehu, tie paši, kas var apstiprināt pārprodukciju.</li>
</ul>
</aside>

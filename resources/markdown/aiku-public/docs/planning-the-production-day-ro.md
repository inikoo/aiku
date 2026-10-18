---
title: Planificarea zilei de producție
summary: Pentru managerul de producție și supervizorul de hală - cum pui sarcinile în planul unei zile fără să numești pe nimeni, cum dai fiecare pas unui operator și ce se întâmplă cu munca nefinalizată în ziua ei.
date: 2026-10-15
source_date: 2026-10-15
tags: production, planning, floor
category: production
series: Ordering from partners
order: 13
---

<aside class="tldr">
Două persoane, două treburi. <b>Managerul</b> hotărăște ce se face într-o zi: pe tabla <b>To produce</b> (De produs) alege cartelele și apasă <b>Plan for this day</b> (planifică pentru această zi), fără să numească pe nimeni. <b>Supervizorul</b> deschide <b>Operations → Day plan</b> (Operațiuni → Planul zilei) și dă fiecare pas al fiecărei sarcini planificate unui operator. Fiecare operator își vede apoi pașii pe ecranul de fabrică. Ce nu e terminat în ziua lui rămâne în plan, în chihlimbariu, până când cineva îl confirmă pentru o zi nouă.
</aside>

## Managerul planifică ziua

Pe **Factory → To produce** (Fabrică → De produs), tabla funcționează ca până acum: cartelele merg din **Backlog** (restanțe) în **Preparing** (în pregătire), unde se stabilește cantitatea, și din **Preparing** în **Assigned** (repartizate), unde se creează ordinul de lucru.

Când o cartelă e lăsată pe **Assigned**, fereastra mică care întreabă *Who makes it?* (cine o face?) începe acum cu o dată și un buton:

1. Alege ziua. Se deschide pe azi.
2. Apasă **Plan for this day**.

Se creează un ordin de lucru pentru acea zi, fără nimeni numit pe el. Fiecare cartelă devine propriul ei ordin de lucru, deci fiecare poate fi ordonat și mutat separat. Mai multe cartele selectate și lăsate împreună sunt planificate împreună, câte un ordin de lucru fiecare.

Alegerea unui nume din lista de mai jos funcționează ca întotdeauna: toată sarcina merge la acea persoană și nu e în planul niciunei zile.

O sarcină care are deja pe cineva numit poate fi pusă și ea într-un plan: apasă creionul de lângă nume pe cardul ei, apoi **Plan for this day**. Numele este scos de pe sarcină, iar supervizorul dă pașii ei.

## Supervizorul dă pașii

**Factory → Operations → Day plan** listează sarcinile planificate pentru zi, un rând pentru fiecare produs de pe o sarcină.

| Coloană | Ce înseamnă |
| --- | --- |
| Order | locul sarcinii în zi - scrie un număr ca s-o muți |
| Job | ordinul de lucru și starea lui |
| Product | ce se face |
| Qty | câte |
| Steps and operators | fiecare pas din rețetă: făcut până acum / cerut, și cui e dat |
| Planned | ziua pentru care a fost planificată |

Fiecare pas are propria listă de nume. Alege unul și pasul apare pe ecranul de fabrică al acelei persoane, și al nimănui altcuiva. Un pas cu chenar chihlimbariu nu are încă pe nimeni; bara de sus îi numără, *3 steps without an operator* (3 pași fără operator), ca să nu rămână nimic uitat.

Ca să schimbi cine face un pas în cursul zilei, alege alt nume. Ca să iei un pas de la cineva, apasă cruciulița din listă. Dacă persoana începuse deja, munca făcută rămâne înregistrată pe numele ei: lista hotărăște doar cine vede pasul de acum încolo.

Data din partea de sus trece la altă zi. Planul de mâine poate fi dat de azi.

## Urmărirea zilei

Aceeași pagină arată cum merge ziua și se reîmprospătează singură:

- bara de sus: câte sarcini, *12 of 18 steps done* (12 din 18 pași făcuți), pași fără operator, sarcini nefinalizate în ziua lor;
- lângă fiecare pas: un cerc gri (neînceput), un cerc chihlimbariu care se învârte (început) sau o bifă verde (făcut), cu numărul făcut până acum;
- *working now* (lucrează acum) și numele, când cineva are pasul deschis pe hală.

Cei care văd pagina dar nu conduc hala văd numele, nu listele.

## Când supervizorul lipsește

Managerul deschide aceeași pagină și dă pașii el însuși. Planificarea și darea pașilor cer aceeași poziție.

## Ce nu s-a terminat

O sarcină planificată cu pași încă deschiși după ziua ei nu este abandonată. Din ziua următoare apare pe Day plan cu data veche în chihlimbariu, iar bara o numără ca *not finished on its day* (nefinalizată în ziua ei). Pentru fiecare, alege:

- **Confirm for** (confirmă pentru) ziua la care te uiți - sarcina trece în planul acelei zile, la sfârșitul ordinii;
- **Take off the plan** (scoate din plan) - sarcina rămâne ordin de lucru, dar nu e în planul niciunei zile.

Până nu apeși una dintre cele două, sarcina rămâne în fața ta în fiecare zi. Pașii ei rămân și pe ecranele de fabrică ale operatorilor cărora le-au fost dați, așa că cine a pus ieri un mix în pauză poate continua în dimineața asta.

Când un operator închide o sarcină cu mai puțin și mută restul într-una nouă (vezi [Lucrul cu ecranul de fabrică](/docs/working-the-floor-screen-ro)), sarcina nouă păstrează ziua planificată, locul în ordine și operatorul fiecărui pas.

## Verificarea de dimineață a materialelor

Depozitul aduce materialele în fabrică în cutii întregi, nu în cantitățile exacte ale zilei. **Factory → Operations → Materials check** (Fabrică → Operațiuni → Verificarea materialelor) listează ce mai are nevoie planul zilei, ca fabrica să verifice ce are și să ceară depozitului restul într-o singură listă clară.

Fiecare rând este un material, însumat pe toate sarcinile zilei, cu sarcinile care îl cer (*JOaroma-0141, JOaroma-0145*). Se numără doar pașii încă nefăcuți: când fabricarea s-a terminat și a rămas doar ambalarea, rândul arată cutiile și etichetele, nu mai sticlele. Mixurile se fac în fabrică și nu apar pe listă. Cantitățile vin din rețete, deci o sarcină a cărei rețetă nu listează materiale nu cere nimic: bara chihlimbarie de deasupra listei numește aceste sarcini, ca golul din rețetă să fie văzut.

| Coloană | Ce înseamnă |
| --- | --- |
| Material | materialul și codul de stoc din depozit |
| For jobs | sarcinile zilei care îl cer |
| Needed | câte consumă pașii rămași de făcut |
| At the factory | câte sunt acum în locația din fabrică și câte au fost deja cerute azi |
| Check | bifează **Have enough** (sunt suficiente) sau scrie aproximativ câte lipsesc |

Când scrii un număr de lipsă, rândul arată ce va aduce depozitul, rotunjit în sus la cutiile lui: dacă lipsesc 1.200 și într-o cutie sunt 500, apare *bring 3 boxes (1.500)* (adu 3 cutii). Un *Warehouse short* (depozitul nu are destul) roșu înseamnă că depozitul nu are atâtea, deci știi imediat.

Apasă **Send to warehouse** (trimite la depozit). Rândurile cu lipsă merg la depozit ca o singură cerere, iar cererea apare deasupra listei, fiecare material marcat *To bring* (de adus), *Delivered* (livrat) sau *Warehouse short*. Dacă bifezi totul ca suficient și trimiți, se înregistrează că verificarea s-a făcut. O a doua verificare mai târziu în zi adaugă doar ce lipsește în continuare.

Managerul sau supervizorul face verificarea sau o dă unui operator prin **Checked by** (verificat de), din partea de sus. Acel operator vede apoi pe ecranul de fabrică un memento care deschide verificarea.

## Depozitul aduce materialele

În depozit, **Goods out → For production** (Ieșiri de marfă → Pentru producție) listează fiecare material cerut de fabrică, în ordinea de parcurs a raftului de unde se ia, cu codul de stoc, cutiile de adus și locația din fabrică unde merg. Cel care pregătește alege raftul, verifică cantitatea și apasă **Delivered**. Când depozitul nu poate aduce, **Warehouse short** anunță fabrica.

Stocul livrat trece de pe raft în locația din fabrică. Rămâne stoc: este numărat și văzut ca înainte, iar o comandă de client îl mai poate culege de acolo când rafturile depozitului sunt goale, rafturile având mereu prioritate. Materialele nefolosite pot fi mutate înapoi pe un raft din pagina locației din fabrică, ca orice altă mutare între locații.

Când o sarcină terminată este recepționată în stoc, materialele ei se consumă mai întâi din locația din fabrică, după cantitățile din rețetă, și abia apoi din alte locații. Numără din când în când locația din fabrică, ca să corectezi spargerile și pierderile.

<aside class="wayfinder"><strong>Unde apeși în aiku</strong>
<ul>
<li><b>Planifici o sarcină pe o zi:</b> <b>Factory</b> → <b>To produce</b> → lasă cartela pe <b>Assigned</b> → alege ziua → <b>Plan for this day</b>.</li>
<li><b>Dai un pas unui operator:</b> <b>Factory</b> → <b>Operations</b> → <b>Day plan</b> → lista de lângă pas.</li>
<li><b>Schimbi ordinea zilei:</b> scrie un număr în <b>Order</b>.</li>
<li><b>Sarcina nefinalizată de ieri:</b> <b>Confirm for</b> ziua, sau <b>Take off the plan</b>.</li>
<li><b>De pe tablă în plan:</b> linkul <b>Day plan</b> de deasupra tablei.</li>
<li><b>Verifici materialele zilei:</b> <b>Factory</b> → <b>Operations</b> → <b>Materials check</b> → <b>Have enough</b> sau un număr de lipsă → <b>Send to warehouse</b>.</li>
<li><b>Dai verificarea unui operator:</b> <b>Materials check</b> → <b>Checked by</b>.</li>
<li><b>Aduci materialele (depozit):</b> <b>Goods out</b> → <b>For production</b> → <b>Delivered</b>, sau <b>Warehouse short</b>.</li>
</ul>
</aside>

<aside class="wayfinder"><strong>Permisiuni de care ai nevoie</strong>
<ul>
<li>Pozițiile se setează pe fișa angajatului sub Human Resources și aduc cu ele drepturile.</li>
<li>Planificarea unei zile, darea pașilor, schimbarea ordinii: poziția <b>Floor supervisor</b> pentru fabrică.</li>
<li>Vizualizarea Day plan fără a o modifica: <b>Foreman</b>.</li>
<li>Lucrul la un pas dat ție: <b>Operative</b>, pe ecranul de fabrică.</li>
<li>Verificarea materialelor: <b>Floor supervisor</b>, sau operatorul căruia i-a fost dată.</li>
<li>Aducerea materialelor în fabrică: o poziție de depozit care lucrează la ieșiri de marfă.</li>
</ul>
</aside>

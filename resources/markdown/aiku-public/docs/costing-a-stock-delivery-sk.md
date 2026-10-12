---
title: Oceňovanie dodávky na sklad
summary: Zadajte, koľko dodávka naozaj stála - faktúru agenta alebo dodávateľa a účty zaplatené na mieste za dopravu, clo a prepravu - aby sa sklad ocenil jeho celkovou nákladovou cenou.
date: 2026-10-12
source_date: 2026-10-12
tags: procurement, stock deliveries, costing, landed cost, service invoices, customs
category: procurement
---

<aside class="tldr">
Náklady na dodávku majú dve časti. Prvá je <b>faktúra agenta alebo dodávateľa</b>: tovar a všetko, čo si k nemu navyše účtovali. Druhá sú <b>landed costs</b> (vedľajšie náklady dovozu): každý účet, ktorý platíte niekomu inému za to, aby sa tovar dostal k vám, napríklad preprava, colné konanie, clo a kamión do skladu. aiku obe časti sčíta, vedľajšie náklady rozdelí medzi položky a sklad ocení touto celkovou sumou. Náklady zadaj ešte pred príchodom tovaru: od 12. októbra 2026 sa tovar nedá uložiť na regál, kým náklady nie sú zadané.
</aside>

## Kde nájdete oceňovanie

Otvorte dodávku cez **Procurement → Stock Deliveries**. Hore na stránke je tabuľka s tromi riadkami:

- **Agent invoice** alebo **Supplier invoice** (faktúra agenta alebo dodávateľa) - čo agent alebo dodávateľ vyfakturoval.
- **Landed costs** (vedľajšie náklady dovozu) - čo sa zaplatilo na mieste za to, aby sa tovar dostal k vám.
- **Landed total** (celkové náklady) - obe časti spolu.

Každý riadok ukazuje svoj **Status**, svoju **Amount** (sumu) v mene vašej organizácie a svoj **Balance due** (zostatok na úhradu). Ak je faktúra v inej mene, jej suma sa zobrazí malým písmom pod tým. Riadky sú na začiatku zbalené; kliknutím na riadok ho otvoríte a uvidíte jeho riadky a tlačidlá.

Náklady môžete zadať hneď po potvrdení dodávky. Nemusíte čakať na tovar.

## Faktúra agenta alebo dodávateľa

Otvorte riadok faktúry a stlačte **Invoice**. Zadajte **Invoice number** (číslo faktúry), **Invoice date** (dátum faktúry), sumu za **Goods** (tovar) a každý vlastný poplatok agenta alebo dodávateľa, označený ako **Freight** alebo **Other**. PDF priložte pod **Invoice file** a stlačte **Save invoice**.

Kým nie je zadaná skutočná faktúra, dodávka zo starého systému ukazuje **Estimated** faktúru (odhad) vytvorenú z toho, čo sa tam zaznamenalo. Uložením skutočnej faktúry sa odhad nahradí a odhad sa ponechá na porovnanie.

V tom istom okne nájdete **Invoice exchange rate** (kurz faktúry), **Settlement** (vyrovnanie) s uplatnenými zálohami a zostatkom na úhradu a náklady načítané z priloženej faktúry.

Pri dodávke nakúpenej cez agenta stlačte **Agent payments**, aby ste schválili poplatky agenta a zaznamenali, čo ste agentovi zaplatili. Poplatky agenta sa do oceňovania započítajú až po vašom schválení.

## Landed costs: jeden riadok na jeden účet

Otvorte riadok **Landed costs** a stlačte **Add** vedľa **Service invoices**. Pre každý účet, ktorý ste dostali, zadajte jednu service invoice:

- druh účtu (pozri zoznam nižšie),
- kto ho poslal a jeho číslo,
- dátum,
- **currency** (mena) a suma - preprava môže byť v dolároch a účet colného agenta v eurách,
- PDF účtu.

Jeden účet, ktorý pokrýva viac dodávok, sa môže medzi ne rozdeliť, podľa hodnoty ich tovaru alebo podľa súm, ktoré zadáte.

Druhy účtov sú tieto:

| Druh | Ako sa rozdelí medzi položky |
| --- | --- |
| Freight (preprava) | podľa hmotnosti |
| Local transport (port to warehouse) (miestna doprava z prístavu do skladu) | podľa hmotnosti |
| Customs duty (clo) | podľa colného riadku, alebo podľa hodnoty, ak nie je zadané colné vyhlásenie |
| Anti-dumping duty (antidumpingové clo) | rovnako ako clo |
| Other import taxes (plastic, excise) (ostatné dovozné dane) | rovnako ako clo |
| Import VAT (DPH pri dovoze) | neoceňuje sa: vráti sa v daňovom priznaní k DPH |
| Customs clearance (colné konanie) | podľa hodnoty |
| Port and terminal charges (prístavné a terminálové poplatky) | podľa hodnoty |
| Storage and demurrage (skladovanie a zdržné) | podľa hodnoty |
| Inspection and certificates (kontrola a certifikáty) | podľa hodnoty |
| Documentation fees (poplatky za dokumenty) | podľa hodnoty |
| Insurance (poistenie) | podľa hodnoty |
| Fines (pokuty) | podľa hodnoty |
| Other (iné) | podľa hodnoty |

Preprava sa rozdelí podľa hodnoty namiesto hmotnosti, keď niektorý produkt v dodávke nemá zadanú hmotnosť; okno vám povie, ktoré produkty to sú.

Každý účet vyplní oceňovanie sám a náklad vyplnený účtom sa dá zmeniť len zmenou samotného účtu. Všetky účty vašej organizácie sú uvedené pod **Service invoices**, ku ktorým sa dostanete z nadpisu tohto zoznamu, a môžete ich filtrovať podľa druhu, odosielateľa, zaplatené alebo nezaplatené a obdobia.

## Zadanie nákladu bez účtu

Stlačte **Edit landed costs** a náklady sa otvoria ako tabuľka. Každý náklad má:

- **N/A** - zaškrtnite, keď dodávka takýto náklad nemá, napríklad žiadne clo pri nákupe v rámci krajiny.
- **Amount** a **Currency** - zadaním sumy sa náklad označí ako prijatý. Mena je na začiatku mena vašej organizácie.
- **Rate** (kurz) - len keď mena nie je ani mena dodávky, ani mena vašej organizácie.
- **Waiting for invoice** (čaká sa na faktúru) - pozri nižšie.
- **Invoice** - cez **Upload** nahrajte PDF, na ktoré sa tento náklad opiera.
- **History** - každá zmena tohto nákladu, s menom toho, kto ju urobil, a časom.

Pod každý náklad môžete pridať poznámku. **Add extra expense** pridá náklad jedného z druhov zo zoznamu vyššie; pri **Other** napíšte, o čo ide.

## Keď účet ešte nedorazil

Pri tomto náklade zaškrtnite **Waiting for invoice** a ak viete, napíšte, o koľko dní ho očakávate. Stránka dodávky potom ukáže, že celková suma sa zvýši.

Ak zadáte sumu, kým náklad čaká, ide o **estimate** (odhad). Odhad sa v oceňovaní počíta, takže dodávku možno ocieniť a sklad ohodnotiť, a dodávka zostane označená **Costed with an estimate**, kým ho nenahradí skutočný účet.

## Keď je oceňovanie hotové

Dodávka je ocenená, keď je zadaná faktúra a jej preprava, clo a ďalšie náklady majú každé sumu, odhad alebo **N/A**. V tej chvíli tovar uložený z dodávky na sklad dostane svoju nákladovú cenu. Riadok **Landed total** potom vypíše každý náklad s jeho percentom z tovaru a povie, koľko nákladov navyše pripadá na každé euro tovaru.

Potom sú náklady zamknuté. Ak ich chcete opraviť alebo nahradiť odhad skutočným účtom, manažér účtovníctva stlačí **Update costing**, urobí zmeny a stlačí **Finish costing**. Sklad sa ocení znova a zmena sa zaznamená s jeho menom a dôvodom.

## Náklady prichádzajú pred tovarom

Pre dodávky vytvorené od 12. októbra 2026:

- **Uloženie na regál je zablokované**, kým faktúra, preprava a clo nemajú každé sumu, odhad alebo **N/A**. Tovar sa dá prijať a skontrolovať, ale nedá sa dať na regál, takže sa nič nepredá za zlú cenu.
- **Označenie dodávky ako prijatej**, kým chýbajú náklady, vyžaduje dôvod. Dôvod sa zapíše do histórie dodávky a vedúci nákupu sa o tom dozvedia.

Dodávky od partnerov sa to netýka: tie sa oceňujú podľa cien z objednávky partnera.

## Čo ešte treba oceniť

Dashboard **Procurement** sa otvára pruhom, ktorý ukazuje, koľko dodávok ešte treba oceniť a koľko z nich je zastavených. Každé číslo otvorí zoznam stock deliveries s príslušným filtrom. V samotnom zozname ponúka filter **Costing** možnosti **To cost**, **Costs missing, put-away blocked** a **Costed with an estimate**.

## Cez vášho AI asistenta

Ľudia, ktorí oceňujú dodávky, môžu požiadať svojho AI asistenta, aby ukázal náklady dodávky alebo uložil jej dopravu, clo a ďalšie náklady. Najprv ukáže, čo sa zmení, a uloží až po vašom potvrdení. Odmietne dodávku, ktorá je už ocenená, a súčet nákladov vyšší než hodnota tovaru, pokiaľ to nepotvrdíte, čo zvyčajne znamená sumu v nesprávnej mene. Každé uloženie sa zapíše do protokolu AI zmien a dá sa tam vrátiť späť. DPH pri dovoze, faktúra agenta alebo dodávateľa a PDF súbory sa zadávajú na stránke, nie cez asistenta.

<aside class="wayfinder"><strong>Kde klikať v aiku</strong>
<ul>
<li><b>Otvoriť oceňovanie dodávky:</b> vaša organizácia → <b>Procurement → Stock Deliveries</b> → dodávka → tabuľka s <b>Agent invoice</b>, <b>Landed costs</b> a <b>Landed total</b>.</li>
<li><b>Zadať faktúru:</b> otvorte riadok faktúry → <b>Invoice</b> → <b>Save invoice</b>.</li>
<li><b>Schváliť poplatky agenta alebo zaznamenať platbu:</b> otvorte riadok faktúry → <b>Agent payments</b>.</li>
<li><b>Pridať účet:</b> otvorte <b>Landed costs</b> → <b>Add</b> vedľa <b>Service invoices</b>.</li>
<li><b>Zadať náklad, označiť ho ako N/A alebo čakajúci:</b> otvorte <b>Landed costs</b> → <b>Edit landed costs</b>.</li>
<li><b>Opraviť ocenenú dodávku (manažéri účtovníctva):</b> <b>Update costing</b> hore na dodávke, potom <b>Finish costing</b>.</li>
<li><b>Pozrieť, čo čaká:</b> dashboard <b>Procurement</b>, pruh hore; alebo filter <b>Costing</b> v zozname stock deliveries.</li>
<li><b>Dovoliť niekomu ukladať náklady cez jeho AI asistenta (administrátori):</b> <b>Sysadmin → Users</b> → otvorte používateľa → <b>Edit</b> → zapnite <b>Can save stock delivery costs through their AI assistant</b>.</li>
</ul>
</aside>

<aside class="permissions"><strong>Oprávnenia, ktoré potrebujete</strong>
Na zobrazenie oceňovania potrebujete oprávnenie zobrazovať nákup alebo účtovníctvo organizácie a na zadávanie nákladov a účtov oprávnenie upravovať nákup alebo účtovníctvo. Zmena už ocenenej dodávky vyžaduje pozíciu manažéra účtovníctva. Ukladanie tovaru na regál a označenie dodávky ako prijatej sa riadia oprávneniami skladového príjmu.
</aside>

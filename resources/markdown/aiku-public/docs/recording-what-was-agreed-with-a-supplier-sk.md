---
title: Zaznamenanie dohody s dodávateľom
summary: Zapíšte si podmienky dohodnuté s dodávateľom alebo agentom - Incoterm, poplatky, menu, dodaciu lehotu - spolu s dôkazom a pridajte poznámky a upozornenia, aby sa zobrazili na každej objednávke a prežili zmenu nákupcu.
date: 2026-10-14
source_date: 2026-10-14
tags: procurement, suppliers, agents, purchase orders, agreed terms, notes, incidents
category: procurement
---

<aside class="tldr">
To, na čom sa s dodávateľom dohodnete, sa zaznamenáva v dvoch podobách. <b>Agreed terms</b> (dohodnuté podmienky) sú obchodné body: Incoterm, čo je zahrnuté v cene, aké poplatky sú povolené a v akej výške, mena, platobné podmienky, dodacia lehota. <b>Notes</b> (poznámky) je všetko ostatné, čo má nákupca vedieť: upozornenie, špecifikácia, pokyn pre agenta, preplatok, ktorý sa stal. Oboje zadáte raz pri dodávateľovi a odvtedy ich aiku zobrazuje pri dodávateľovi, jeho produktoch, agentovi a na každej objednávke. Tie, ktoré vyberiete, vytlačí aj na objednávke odoslanej dodávateľovi.
</aside>

## Kde to nájdete

Otvorte dodávateľa cez **Procurement → Suppliers**. Prvá karta sa otvorí s panelom **What was agreed** (čo sa dohodlo): platné podmienky, pripnuté alebo kritické poznámky a počet otvorených incidentov. Detail obsahujú ďalšie dve karty:

- **Agreed terms** - všetky podmienky, najprv platné, potom nahradené alebo odvolané.
- **Notes** - každá poznámka napísaná k dodávateľovi a k jeho produktom.

Rovnaký panel a tie isté dve karty nájdete pri každom **produkte dodávateľa** a pri každom **agentovi**. Pri agentovi má panel navyše **Suppliers to watch** (dodávatelia na sledovanie): dodávatelia agenta, ktorí majú upozornenie alebo otvorený incident.

## Pridanie dohodnutej podmienky

Na karte **Agreed terms** stlačte **Add agreed term** a vyplňte:

- **Term** (podmienka) - čoho sa podmienka týka: Incoterm, Packing charge, Label charge, Barcode charge, Carton marking charge, Insert or leaflet charge, Mould or tooling charge, Sample charge, Local delivery to port charge, Payment currency, Payment terms, Lead time in days, Minimum order alebo Other charge.
- **What was agreed** - **Included** (je v cene), **Not allowed** (dodávateľ to nesmie účtovať), suma (**Fixed amount**, **Per unit**, **Per carton**, **Per order**, **Percentage**) alebo **Text**.
- **Amount**, **Currency** a **Tolerance** - zobrazia sa, keď ste vybrali sumu. Tolerancia je rozdiel, ktorý akceptujete, kým sa počíta ako odchýlka.
- Textový riadok - Incoterm a jeho miesto (napríklad FOB Ningbo), na čo slúži Other charge alebo akýkoľvek detail.
- **Agreed on** a **Agreed with** - dátum a kontakt u dodávateľa alebo agenta.
- **Evidence** (dôkaz) - e-mail, chat alebo dokument, v ktorom sa to dohodlo.
- **Who may see it** (kto to môže vidieť) - **Internal**, **Agent** alebo **Supplier**.
- **Print on the purchase order** - zapnite, aby sa podmienka vytlačila na objednávke.

Poplatok so sumou sa nedá uložiť bez dôkazu. aiku vás zaznamená ako osobu, ktorá podmienku dohodla.

<aside class="tip">
Aby sa podmienka zobrazila na objednávke, musia byť splnené obe veci: zapnuté <b>Print on the purchase order</b> a <b>Who may see it</b> nastavené na Supplier alebo Agent. Interná podmienka sa nikdy nevytlačí.
</aside>

## Jedna organizácia alebo všetky

Dodávateľa zdieľajú všetky organizácie, ktoré od neho nakupujú, a rovnako aj podmienku: čo zaznamená jeden nákupca, platí pre všetkých. Ak má vaša organizácia inú dohodu, zapnite **Only for this organisation**. Vaša organizácia potom vidí svoju podmienku a ostatné si ponechajú spoločnú.

## Podmienka pre jeden produkt

Podmienka zadaná pri **produkte dodávateľa** nahrádza podmienku dodávateľa rovnakého druhu len pre tento produkt. Stránka produktu ukazuje, odkiaľ každá podmienka pochádza: **This product**, **Supplier** alebo **Agent**. Na karte **Agreed terms** produktu je podmienka dodávateľa, ktorú nahrádza, označená **Overridden**.

## Zmena alebo odvolanie podmienky

Podmienky sa nikdy neupravujú ani nemažú. Stlačte **Change** pri podmienke, zadajte, na čom sa teraz dohodlo, a uložte: nová podmienka platí a stará zostane v zozname ako **Superseded** s dátumom, kedy prestala platiť. Stlačte **Withdraw**, keď podmienka už neplatí a nič ju nenahrádza.

Podmienku môže zmeniť alebo odvolať osoba, ktorá ju dohodla, a vedúci obstarávania.

## Notes

Na karte **Notes** stlačte **Add note**. Napíšte jeden riadok ako nadpis a v prípade potreby detail pod ním. Potom vyberte:

- **category** (kategóriu), ktorá určuje farbu poznámky;
- **severity** (závažnosť): Info, Warning alebo Critical;
- kto ju môže vidieť: Internal, Agent alebo Supplier. Nová poznámka je interná;
- **Pin: show on purchase orders** pre poznámku, ktorú musí vidieť každý nákupca pri objednávaní.

| Kategória | Farba | Na čo ju použiť |
| --- | --- | --- |
| Agent | Fialová | Pokyny pre agenta k tomuto dodávateľovi |
| Product | Modrá | Dohodnutá špecifikácia, materiál, veľkosť, povrchová úprava |
| Packaging | Zelená | Vnútorný a vonkajší obal, štítky, vložky |
| Charges | Červená | Dohodnuté alebo sporné poplatky |
| Delivery | Oranžová | Dodacie lehoty, sezónne omeškania, preprava |
| Currency and payment | Žltá | Mena, záloha, spôsob platby |
| Incident | Čierna | Preplatok alebo chybná dodávka, ktorá sa stala |
| General | Sivá | Všetko ostatné a poznámky napísané pred vznikom kategórií |

Pri **Incident** môžete zadať preplatenú alebo reklamovanú sumu a jej menu. Incident začína ako otvorený a v paneli sa počíta, kým nie je vyriešený.

Ani poznámky sa nemažú. Stlačte **Resolve**, keď poznámka už neplatí; zostane v zozname, preškrtnutá. **Pin** a **Unpin** presúvajú poznámku do panela a z neho. Autor poznámky a vedúci obstarávania ju môžu pripnúť a vyriešiť.

## Na objednávke

Otvorte objednávku dodávateľa. Nad riadkami panel **What was agreed** ukazuje platné podmienky dodávateľa a produktov na objednávke a pripnuté alebo kritické poznámky k objednávke, dodávateľovi, jeho agentovi a týmto produktom.

Ak je niektorá z týchto poznámok **Critical**, pred stlačením **Submit** zaškrtnite **I have read the critical notes for this order**. Bez zaškrtnutia sa objednávka neodošle.

Karta **Notes** na objednávke a na dodávke na sklad má rovnaký formulár ako pri dodávateľovi, takže incident môžete zapísať na objednávke, kde sa stal; potom je s ňou prepojený.

## Čo vidí dodávateľ a agent

PDF objednávky obsahuje blok **Agreed terms** a blok **Notes** a e-mail, ktorým sa odosiela, uvádza dohodnuté podmienky. Zahŕňajú len to, čo je označené pre dodávateľa a pri objednávke cez agenta aj pre agenta. Interné podmienky a poznámky sa nikdy nevytlačia.

Agent, ktorý pracuje v aiku, vidí na svojich stránkach len podmienky a poznámky označené ako viditeľné pre agenta a nemôže žiadne pridávať ani meniť.

## Odovzdanie dodávateľa

Pri dodávateľovi stlačte **Handover sheet** v paneli **What was agreed**. Otvorí sa PDF so všetkými platnými podmienkami a všetkými otvorenými poznámkami, určené osobe, ktorá dodávateľa preberá.

## Keď sa objednávka alebo faktúra líši od dohodnutého

aiku porovnáva objednané a fakturované s dohodnutými podmienkami pri každom dodávateľovi alebo agentovi, ktorý má zaznamenanú aspoň jednu dohodnutú podmienku. Dodávateľ ani agent nemôže sám zvýšiť cenu ani pridať poplatok: každý rozdiel čaká, kým ho nákup prijme.

- **Na nákupnej objednávke:** riadok, ktorý stojí viac ako náklad produktu dodávateľa, alebo objednávka v inej mene, ako je dohodnutá mena platby.
- **Na faktúre dodávateľa alebo agenta k dodávke:** poplatok, ktorého podmienka je Zahrnuté alebo Nepovolené, poplatok nad dohodnutou sumou, poplatok bez dohodnutej podmienky, alebo doprava, keď dohodnutý Incoterm (CFR, CIF, CPT, CIP, DAP, DPU, DDP) ukladá zaplatiť ju dodávateľovi.

Pri zadávaní faktúry zvoľte typ každého poplatku (poplatok za balenie, poplatok za etikety, doprava a pod.), aby sa porovnal so správnou podmienkou.

Každý rozdiel je červeno uvedený v paneli **Iné, ako bolo dohodnuté** hore na objednávke alebo dodávke, vedľa toho, čo bolo dohodnuté. Napíšte, prečo ho prijímate, a stlačte **Prijať**. Kým nie sú prijaté všetky, objednávku nemožno odoslať, dodávku nemožno zaplatiť a poplatky agenta nemožno schváliť. Ak sa objednávka alebo faktúra neskôr zmení, nový rozdiel treba prijať znova.

Každý prijatý rozdiel sa stane otvorenou poznámkou **Incident** na dodávateľovi alebo agentovi so sumou a vaším dôvodom a dostane o ňom upozornenie vedúci nákupu a nákupca objednávky. Zoznamy dodávateľov ukazujú počet otvorených incidentov vedľa kódu dodávateľa.

<aside class="wayfinder"><strong>Kde klikať v aiku</strong>
<ul>
<li><b>Pozrieť, čo sa s dodávateľom dohodlo:</b> vaša organizácia → <b>Procurement → Suppliers</b> → dodávateľ → panel <b>What was agreed</b>.</li>
<li><b>Pridať podmienku:</b> dodávateľ → <b>Agreed terms</b> → <b>Add agreed term</b>.</li>
<li><b>Zmeniť alebo odvolať podmienku:</b> dodávateľ → <b>Agreed terms</b> → <b>Change</b> alebo <b>Withdraw</b> v jej riadku.</li>
<li><b>Pridať poznámku:</b> dodávateľ → <b>Notes</b> → <b>Add note</b>.</li>
<li><b>Pripnúť alebo vyriešiť poznámku:</b> dodávateľ → <b>Notes</b> → <b>Pin</b> alebo <b>Resolve</b> v jej riadku.</li>
<li><b>Podmienka alebo poznámka pre jeden produkt:</b> dodávateľ → jeho produkty → produkt → <b>Agreed terms</b> alebo <b>Notes</b>.</li>
<li><b>Potvrdiť kritické poznámky:</b> objednávka → zaškrtnite <b>I have read the critical notes for this order</b> → <b>Submit</b>.</li>
<li><b>Vytlačiť odovzdávací list:</b> dodávateľ → panel <b>What was agreed</b> → <b>Handover sheet</b>.</li>
<li><b>Prijať rozdiel:</b> nákupná objednávka alebo dodávka → <b>Iné, ako bolo dohodnuté</b> → napíšte dôvod → <b>Prijať</b>.</li>
</ul>
</aside>

<aside class="permissions"><strong>Oprávnenia, ktoré potrebujete</strong>
Na zobrazenie podmienok a poznámok potrebujete oprávnenie zobrazovať obstarávanie organizácie a na pridanie poznámky alebo podmienky oprávnenie upravovať obstarávanie. Zmena alebo odvolanie podmienky, ktorú dohodol niekto iný, a pripnutie alebo vyriešenie poznámky napísanej niekým iným vyžadujú pozíciu vedúceho obstarávania. Prijatie rozdielu oproti dohodnutému vyžaduje oprávnenie upravovať nákup v organizácii, ktorá platí.
</aside>

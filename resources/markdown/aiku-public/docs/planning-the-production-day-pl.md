---
title: Planowanie dnia produkcji
summary: Dla kierownika produkcji i kierownika hali - wstawianie zadań do planu dnia bez wskazywania osoby, przydzielanie każdego kroku pracownikowi oraz co dzieje się z pracą niezakończoną w swoim dniu.
date: 2026-10-12
source_date: 2026-10-12
tags: production, planning, floor
category: production
series: Ordering from partners
order: 13
---

<aside class="tldr">
Dwie osoby, dwa zadania. <b>Kierownik produkcji</b> decyduje, co powstaje danego dnia: na tablicy <b>To produce</b> (Do produkcji) wybiera karty i naciska <b>Plan for this day</b> (Zaplanuj na ten dzień), nie wskazując nikogo. <b>Kierownik hali</b> (Floor supervisor) otwiera <b>Operations → Day plan</b> (Operacje → Plan dnia) i przydziela pracownikowi każdy krok każdego zaplanowanego zadania. Każdy pracownik widzi potem swoje kroki na ekranie hali. Wszystko, co nie zostało skończone w swoim dniu, zostaje w planie na bursztynowo, dopóki ktoś nie potwierdzi tego na nowy dzień.
</aside>

## Kierownik produkcji planuje dzień

Na **Factory → To produce** (Fabryka → Do produkcji) tablica działa jak dotąd: karty przechodzą z **Backlog** (Zaległe) do **Preparing** (W przygotowaniu), gdzie ustawia się ilość, a z **Preparing** do **Assigned** (Przypisane), gdzie powstaje zlecenie produkcyjne.

Gdy karta zostaje upuszczona na **Assigned**, małe okno z pytaniem *Kto to robi?* zaczyna się teraz od daty i przycisku:

1. Wybierz dzień. Okno otwiera się na dzisiaj.
2. Naciśnij **Plan for this day**.

Powstaje zlecenie produkcyjne na ten dzień, bez wskazanej osoby. Każda karta staje się osobnym zleceniem, więc każde można ustawiać w kolejności i przesuwać osobno. Kilka kart zaznaczonych i upuszczonych razem jest planowanych razem, po jednym zleceniu na kartę.

Wybranie nazwiska z listy poniżej działa tak jak zawsze: całe zadanie trafia do tej osoby i nie ma go w planie żadnego dnia.

Zadanie, które ma już wskazaną osobę, też można wstawić do planu: naciśnij ołówek obok nazwiska na jego karcie, a potem **Plan for this day**. Nazwisko zostaje zdjęte z zadania, a jego kroki przydziela kierownik hali.

## Kierownik hali przydziela kroki

**Factory → Operations → Day plan** wyświetla zadania zaplanowane na dany dzień, jeden wiersz na produkt w zadaniu.

| Kolumna | Znaczenie |
| --- | --- |
| Order (Kolejność) | miejsce zadania w dniu - wpisz numer, żeby je przesunąć |
| Job (Zadanie) | zlecenie produkcyjne i jego stan |
| Product (Produkt) | co jest wytwarzane |
| Qty (Ilość) | ile |
| Steps and operators (Kroki i pracownicy) | każdy krok receptury: zrobiono dotąd / zamówiono, oraz komu jest przydzielony |
| Planned (Zaplanowano) | dzień, na który zaplanowano |

Każdy krok ma własną listę nazwisk. Wybierz jedno, a krok pojawi się na ekranie hali tej osoby i niczyim innym. Krok z bursztynową ramką nie ma jeszcze nikogo; pasek u góry je zlicza, *3 kroki bez pracownika*, więc nic nie zostanie pominięte przez pomyłkę.

Aby zmienić, kto wykonuje krok w ciągu dnia, wybierz inne nazwisko. Aby odebrać krok komuś, naciśnij mały krzyżyk na liście. Jeśli ta osoba już zaczęła, jej praca zostaje zapisana na jej nazwisko: lista decyduje tylko o tym, kto widzi krok od teraz.

Data u góry przenosi na inny dzień. Plan na jutro można przydzielić już dzisiaj.

## Śledzenie dnia

Ta sama strona pokazuje, jak idzie dzień, i odświeża się sama:

- pasek u góry: ile zadań, *12 z 18 kroków zrobionych*, kroki bez pracownika, zadania niezakończone w swoim dniu;
- obok każdego kroku: szare kółko (niezaczęty), bursztynowy znacznik ładowania (zaczęty) lub zielony ptaszek (zrobiony), z dotychczasową liczbą wykonanych sztuk;
- *working now* (teraz pracuje) i nazwisko, gdy ktoś ma ten krok otwarty na hali.

Osoby, które widzą stronę, ale nie prowadzą hali, widzą nazwiska, nie listy.

## Gdy kierownika hali nie ma

Kierownik produkcji otwiera tę samą stronę i sam przydziela kroki. Planowanie i przydzielanie kroków wymagają tego samego stanowiska.

## Co nie zostało skończone

Zaplanowane zadanie z otwartymi krokami po upływie swojego dnia nie przepada. Od następnego dnia pokazuje się w Day plan ze starą datą na bursztynowo, a pasek liczy je jako *niezakończone w swoim dniu*. Dla każdego z nich wybierz:

- **Confirm for** (Potwierdź na) dzień, który właśnie oglądasz - zadanie przechodzi do planu tego dnia, na koniec kolejności;
- **Take off the plan** (Zdejmij z planu) - zadanie zostaje jako zlecenie produkcyjne, ale nie ma go w planie żadnego dnia.

Dopóki nie zostanie naciśnięte jedno z dwóch, zadanie zostaje przed Tobą każdego dnia. Jego kroki zostają też na ekranach hali pracowników, którym je przydzielono, więc ktoś, kto wczoraj zatrzymał mieszankę, może ją dokończyć dziś rano.

Gdy pracownik zamyka zadanie z mniejszą ilością i przenosi resztę do nowego zadania (zobacz [Praca z ekranem hali](/docs/working-the-floor-screen-pl)), nowe zadanie zachowuje zaplanowany dzień, miejsce w kolejności i pracownika każdego kroku.

<aside class="wayfinder"><strong>Gdzie kliknąć w aiku</strong>
<ul>
<li><b>Zaplanuj zadanie na dzień:</b> <b>Factory</b> → <b>To produce</b> → upuść kartę na <b>Assigned</b> → wybierz dzień → <b>Plan for this day</b>.</li>
<li><b>Przydziel krok pracownikowi:</b> <b>Factory</b> → <b>Operations</b> → <b>Day plan</b> → lista obok kroku.</li>
<li><b>Zmień kolejność dnia:</b> wpisz numer w <b>Order</b>.</li>
<li><b>Wczorajsze niezakończone zadanie:</b> <b>Confirm for</b> dzień, albo <b>Take off the plan</b>.</li>
<li><b>Z tablicy do planu:</b> link <b>Day plan</b> nad tablicą.</li>
</ul>
</aside>

<aside class="wayfinder"><strong>Wymagane uprawnienia</strong>
<ul>
<li>Stanowiska są ustawiane na karcie pracownika w module Human Resources i niosą ze sobą uprawnienia.</li>
<li>Planowanie dnia, przydzielanie kroków, zmiana kolejności: <b>Floor supervisor</b> dla fabryki.</li>
<li>Podgląd Day plan bez zmian: <b>Foreman</b>.</li>
<li>Praca nad przydzielonym krokiem: <b>Operative</b>, na ekranie hali.</li>
</ul>
</aside>

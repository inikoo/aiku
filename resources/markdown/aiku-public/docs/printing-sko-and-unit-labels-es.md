---
title: Imprimir etiquetas de SKO y de unidad
summary: Imprime la etiqueta que va en el producto y la que va en la caja, desde la página del SKO o desde una línea de un albarán, en el rollo que ya compras o 27 por hoja A4.
date: 2026-10-13
source_date: 2026-10-13
tags: warehouse, inventory, labels, barcodes, printing
category: warehouse
---

<aside class="tldr">
Un SKO puede imprimir <b>dos etiquetas distintas</b>. La <b>etiqueta de unidad</b> (unit label) va en el producto que acaba en manos del cliente, así que lleva el origen, el fabricante, el peso y la dirección de tu empresa, y se construye alrededor del EAN13 de unidad. La <b>etiqueta de SKO</b> (SKO label) va en la caja exterior, así que dice qué es la caja y cuántas unidades hay dentro, en un tamaño que se lee desde unos metros de distancia en el pasillo. Abre cualquiera de las dos desde la página del SKO o desde una línea de un albarán, marca lo que quieras que aparezca, elige el tamaño de etiqueta e imprime una o una hoja de 27.
</aside>

## La etiqueta unificada

El panel de etiquetas se abre en **Unified**: una única etiqueta de unidad, de SKO y de caja (carton) que es igual en todos los centros, de modo que el stock se mueve entre el Reino Unido, España y Eslovaquia sin tener que reetiquetarlo. El selector **Unified / Localised** de la parte superior del panel te lleva de vuelta a las etiquetas descritas en el resto de esta página, para los pocos casos que necesitan una etiqueta de una sola organización.

| | Unidad | SKO | Caja (carton) |
| --- | --- | --- | --- |
| Tamaño | 100 × 60 mm | 100 × 50 mm | 100 × 70 mm |
| Código de barras | el EAN-13 de unidad | GS1-128 (el estándar de códigos logísticos) con un GTIN-14 que empieza por 1, y después el lote | GS1-128 con un GTIN-14 que empieza por 2, y después el lote |
| Se imprime | siempre | solo cuando el SKO contiene más de una unidad | siempre |

Las tres se imprimen una etiqueta por página en un rollo de 100 mm de ancho. No hay nada que marcar: la etiqueta de unidad lleva el nombre en todos los idiomas en los que la trade unit lo tiene, el país de origen, el material, el lote, las advertencias y los tres importadores; la etiqueta de SKO indica cuántas unidades hay en el paquete; la etiqueta de caja añade unidades por paquete, paquetes y unidades por caja, pesos, origen y código HS (código arancelario).

El paquete y la caja reciben un número propio, calculado a partir del EAN-13 de unidad, de modo que un paquete ya no se escanea como si fuera una unidad. La página del escáner de códigos de barras lee estos códigos nuevos y sigue leyendo los números que ya hay en las estanterías.

**El código de lote es lo único que escribes.** Se propone como el código del proveedor y el mes (por ejemplo `PKS202610`) y admite hasta 20 caracteres: de la A a la Z, del 0 al 9 y guion.

**Una etiqueta unificada a la que le falta algo no se imprime.** El panel enumera lo que falta y el botón PDF permanece apagado hasta que se corrige el registro:

- la unidad no tiene un EAN-13 válido;
- falta el nombre en español o en eslovaco en la trade unit;
- falta el país de origen;
- el producto tiene advertencias, pero ninguna en inglés, español o eslovaco;
- la etiqueta de caja necesita las unidades por caja del producto del proveedor;
- los textos no caben en la etiqueta.

El peso de la caja solo se imprime cuando el producto del proveedor tiene tanto el peso neto como el bruto y el neto no supera al bruto.

## Las dos etiquetas localizadas

No son la misma etiqueta en dos tamaños. Son para dos lectores distintos.

| | Etiqueta de unidad | Etiqueta de SKO |
| --- | --- | --- |
| Va en | el producto | la caja exterior |
| La lee | quien acaba teniéndolo en la mano | cualquiera que pase por el pasillo |
| Código de barras | el EAN13 de unidad | el código de SKO, a todo lo ancho |
| Puede llevar | imagen, país de origen, fabricante, peso, texto libre, firma de la cuenta | imagen, texto libre |
| Tamaños | siete | cuatro |

La etiqueta de unidad es la de la letra pequeña. Dice de dónde vienen los artículos y quién los fabricó, indica el peso y firma la organización debajo, porque para eso sirve una etiqueta en un producto. La etiqueta de SKO prescinde de todo eso: el código va en blanco sobre negro, la cantidad y el nombre debajo, y el código de barras ocupa todo el ancho de la etiqueta.

## Imprimir una

Abre un SKO y busca los códigos de barras. Pulsa cualquiera de los dos — el de SKO o el EAN13 de unidad — y el panel de etiquetas se abre sobre ese. Si solo uno de los dos se puede imprimir, el panel se abre sobre ese; si se pueden los dos, aparece arriba un pequeño selector **SKO / Unit** y puedes cambiar sin cerrar el panel.

Desde un albarán es más rápido. Cada línea de la pestaña **Items** tiene un pequeño icono de PDF junto al código. Púlsalo y se abre el mismo panel para ese SKO, sin salir del albarán.

## Elegir qué aparece

Cada elemento es una casilla, y lo que viene marcado por defecto es lo que Aurora imprimía por defecto: todo aquello para lo que el SKO tiene un valor, salvo la firma de la cuenta y el texto libre, que quedan apagados hasta que los pidas.

**Una casilla que no se puede pulsar significa que el SKO no tiene nada que imprimir ahí.** Pasa el ratón por encima y te dice cuál — *This item has no image* (no tiene imagen), *This item has no country of origin* (no tiene país de origen), *This item has no manufacturer* (no tiene fabricante), *This item has no weight* (no tiene peso). Eso lo dicen los datos del SKO, no la etiqueta: rellena el campo en la trade unit y la casilla se activa. Funciona así para que nadie imprima una etiqueta con un hueco.

**El texto libre** (custom text) es texto solo para esta impresión. No se guarda en el SKO. Úsalo para lo que cambia entre tiradas y no pertenece al registro de nadie.

## Tamaños y hojas

Los tamaños son los mismos soportes que ofrecía Aurora, al milímetro, de modo que un rollo comprado para el sistema antiguo sigue imprimiendo tal cual en este.

| | Tamaños disponibles |
| --- | --- |
| Etiqueta de unidad | 63 × 29,6, 63,5 × 29,6, 70 × 29,7, 70 × 30, 125 × 37, 130 × 60, 140 × 90 |
| Etiqueta de SKO | 63 × 29,6, 63,5 × 29,6, 70 × 29,7, 130 × 60 |

La etiqueta de SKO se ofrece en cuatro porque lleva mucho menos, y esos cuatro son los que le van bien a una caja.

**Layout** (disposición) es una etiqueta suelta, cortada a su propio tamaño, o **A4 27 labels (EU30161)** — tres a lo ancho y nueve a lo alto en una hoja A4 normal. La hoja está troquelada a 63,5 × 29,6, así que al elegirla el selector de tamaño se aparta y lo indica. Las 27 son la misma etiqueta; una hoja sirve para imprimir una tirada de un solo SKO, no una página mezclada.

El texto, el código de barras y la imagen se escalan con la etiqueta que elijas en lugar de ser fijos, así que una de 140 × 90 llena su espacio y una de 63 × 29,6 sigue siendo legible.

## Cuando una etiqueta no se imprime

La etiqueta de unidad se construye alrededor de su código de barras, así que un SKO sin EAN13 de unidad no puede imprimirla. El panel lo dice y el botón PDF se apaga. Pon primero el EAN en el SKO — el EAN13 de unidad tiene su propio editor en la página del SKO — y la etiqueta se imprime.

La etiqueta de SKO es más indulgente. Se imprime para una caja a la que todavía no se le ha asignado código de barras; sencillamente se imprime sin él. Consulta [Comprobar códigos de barras de SKO con un escáner](/docs/checking-sko-barcodes-with-a-scanner) para poner el código exterior en el SKO correcto.

## De dónde sale cada línea

En la etiqueta no se escribe nada a mano salvo el texto libre. Cada línea se lee del registro, así que corregir una etiqueta es corregir el registro.

| En la etiqueta | Sale de |
| --- | --- |
| Código y nombre | el código del SKO y el nombre de su trade unit |
| **6x** delante del nombre | cuántas unidades van empaquetadas en la caja |
| *Imported from … by …* | el país de origen de la trade unit y tu organización |
| *Manufactured by …* | el fabricante GPSR de la trade unit |
| Peso | el peso de marketing de la trade unit |
| El bloque de dirección | la dirección y el teléfono de tu organización |
| Imagen | la primera trade unit que tiene imagen principal |

Cuando un SKO contiene varias trade units que no coinciden en alguno de estos datos, la línea se omite en lugar de adivinarla: una etiqueta que nombra un solo país para una caja con artículos de dos sería incorrecta, no simplemente incompleta.

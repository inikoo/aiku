---
title: Trabajar la pantalla de planta
summary: La guía del artesano - la pantalla Mis tareas, la lista de trabajos que se actualiza sola, START y DONE, y qué pulsar cuando hiciste menos de lo pedido.
date: 2026-10-12
source_date: 2026-10-12
tags: production, floor, artisan
category: production
series: Ordering from partners
order: 9
---

<aside class="tldr">
Para las personas que fabrican. <b>Factory → Jobs</b> (Fábrica → Trabajos) es todo tu día en una sola pantalla: los trabajos asignados a ti a la izquierda, el que estás haciendo en el medio. Pulsa <b>START</b> (Empezar), hazlo, escribe cuántos hiciste, pulsa <b>DONE</b> (Terminado). Si hiciste menos de lo pedido, la pantalla te hace una sola pregunta: pausar y seguir más tarde, cerrar el trabajo y pasar el resto a uno nuevo, o terminarlo aquí. No hay nada más que rellenar.
</aside>

## La pantalla

La página está organizada como una bandeja de entrada.

**Izquierda, siempre visible:** la lista. Dos contadores arriba - unidades hechas hoy y tareas terminadas hoy. Debajo, <b>Your jobs</b> (Tus trabajos), el trabajo asignado a ti: trabajos enteros con tu nombre, y pasos sueltos que el supervisor te dio en el plan del día (ver [Planificar el día de producción](/docs/planning-the-production-day-es)). Un trabajo puede tener su fabricación con una persona y su embalaje con otra; cada una ve solo su propio paso. Si tu puesto te permite coger trabajos del grupo abierto, sigue una sección <b>Open jobs</b> (Trabajos abiertos). Al final, <b>Finished today</b> (Terminado hoy), lo que ya has cerrado.

**Derecha:** lo que hayas seleccionado. Antes de empezar, son los detalles del trabajo con un botón grande <b>START</b>. Una vez empezado, es la tarjeta de trabajo con el reloj.

La lista no necesita actualizarse a mano. Cuando un planificador te asigna algo, cuando un compañero empieza uno de los trabajos abiertos, cuando tú cierras uno, la lista cambia sola, en todas las pantallas que muestran esa fábrica.

## Una fila de la lista

Cada fila es una tarea de una orden de trabajo:

| Línea | Significado |
| --- | --- |
| Código | el producto a fabricar - SKO-01 |
| Nombre | su nombre |
| Tarea · orden de trabajo | el paso (Production, Labelling...) y la referencia de la orden de trabajo |
| 0/25 | hecho hasta ahora / pedido |

Una fila con una ▶ verde y una hora es la que estás trabajando, y cuándo la empezaste. Una nota ámbar bajo una fila significa que espera una mezcla, o que otra persona ya la tiene abierta.

## Hacer un trabajo

1. Toca la fila. Los detalles aparecen a la derecha.
2. Pulsa <b>START</b> (Empezar). El reloj arranca y la fila recibe su marca verde.
3. Fabrícalo.
4. Escribe el número que hiciste en <b>Quantity made</b> (Cantidad hecha). Si eres capataz o superior, hay también una casilla <b>Rejected</b> (Rechazado) para las piezas que no pasaron.
5. Pulsa <b>DONE</b> (Terminado).

¿Hiciste todo lo que se pedía? Eso es todo. La tarea se cierra, la orden de trabajo termina y se avisa al almacén de que hay algo que guardar - ver [Guardar la producción terminada](/docs/putting-away-finished-production-es).

## Cuando hiciste menos de lo pedido

Escribe el número real y pulsa <b>DONE</b>. La fila de entrada se sustituye por un panel corto: *19 hechos · 6 pendientes* y cuatro botones.

- <b>Pause</b> (Pausar) - misma mezcla, sigues más tarde. El trabajo queda abierto con la misma referencia y el mismo código de lote, y la cuenta sigue desde 19. Úsalo cuando el día termina antes que la mezcla.
- <b>Close at 19 and move remaining 6 to new job …</b> (Cerrar en 19 y pasar los 6 restantes a un trabajo nuevo) - el trabajo se cierra en 19 y los 6 pasan a una nueva orden de trabajo con la letra siguiente (0127 pasa a 0127a) y su propio código de lote. Úsalo cuando el resto será una mezcla nueva. La pantalla te pide confirmar con <b>Yes, close and move</b> (Sí, cerrar y pasar), porque un trabajo cerrado no se puede continuar.
- <b>Job finished</b> (Trabajo terminado) - el trabajo se cierra en 19 y ahí acaba. El planificador ve una orden de trabajo que pedía 25 y consiguió 19.
- <b>Back</b> (Atrás) - cambiar el número.

Un trabajo pasado a uno nuevo muestra <b>Continued from 0127</b> (Continuación de 0127) en su tarjeta, y el de origen muestra <b>Continued in 0127a</b> (Continuado en 0127a). El trabajo nuevo sigue con las mismas personas y en el plan del mismo día.

## Cuando hiciste más de lo pedido

Escribe el número real y pulsa **DONE**. La pantalla avisa de las unidades por encima del objetivo y abre **Autorización del responsable para sobreproducción**.

- **Escanear la tarjeta** - un responsable o supervisor enseña a la cámara su QR personal (el mismo con el que ficha). Se acepta en cuanto se lee. **Cambiar cámara** alterna entre la cámara delantera y la trasera.
- **Usar PIN del responsable** - si la cámara no lo lee, el responsable escribe su PIN de fichaje.

Solo puede autorizar quien dirige esta planta, y nunca su propio trabajo. Tras cinco tarjetas o PIN erróneos la ventana se bloquea 15 minutos.

Una vez autorizado, el trabajo crece a lo realmente hecho: el almacén guarda todo, lo que ningún pedido pidió va a stock y las materias primas se descuentan por el lote entero. Los pasos siguientes del mismo trabajo crecen también. La página de la orden de trabajo muestra quién hizo el extra, quién lo autorizó, cómo y cuándo.

## Un lote para varias líneas

A veces un mismo lote sirve a más de un trabajo. Por ejemplo, 100 panes HCS-48 y 100 panes cortados SLHCS-48 con el mismo aroma se mezclan, se vierten y se moldean juntos como 200 panes.

**Combinar (responsables).** Toca una de las líneas en la pantalla de planta. Bajo sus detalles, **Combine Production with other lines into one batch** lista las otras líneas abiertas que esperan el mismo paso. Marca las que van en el mismo lote y pulsa **Combine**. No se ofrece una línea que ya esté combinada, terminada o en la que alguien esté trabajando.

**Trabajarlo (artesanos).** Las líneas se ven ahora como una sola fila con un 🔗 y los códigos juntos, *HCS-48 + SLHCS-48*, y el total, *0/200*. Los detalles listan cada línea con su orden de trabajo. Pulsa **START** una vez, haz el lote, escribe el total hecho - 200 - y pulsa **DONE** una vez.

**Qué hace Aiku con ello.** El total se reparte entre las líneas según lo que le faltaba por hacer a cada una, en unidades enteras: 100 para HCS-48, 100 para SLHCS-48. El tiempo se reparte igual. Después cada línea sigue con sus propios pasos siguientes - encogido para una, cortado y luego encogido para la otra. La página de la orden de trabajo muestra el paso como *One batch with* la otra línea.

**El pago y los objetivos** se calculan sobre el lote en conjunto: 200 panes en las horas que llevó, frente al objetivo del paso. Combinar paga exactamente lo mismo que lotes separados a la misma velocidad. Cuando las líneas tienen objetivos distintos para el paso, el objetivo del lote es el que tarda las mismas horas que los lotes separados.

Hacer más de lo que pidieron todas las líneas requiere la tarjeta o el PIN de un responsable, como arriba; el extra también se reparte entre las líneas. Un responsable puede **Separate** las líneas de nuevo siempre que nadie esté trabajando en ellas. Lo que ya se hizo junto se queda con cada línea.

## Cosas que conviene saber

- **Un trabajo a la vez.** Mientras tienes un trabajo abierto, los botones START están desactivados. Ciérralo primero.
- **Tu pago por la tarea** sigue el número en Quantity made, como se explica en [Puestos de fábrica](/docs/factory-positions-es). Las piezas rechazadas no se pagan.
- **Open jobs** (Trabajos abiertos) son trabajos y pasos sin nadie asignado, o con otra persona asignada. Coger uno está bien si tu puesto lo permite; a partir de entonces es tuyo hasta que se cierre.

<aside class="wayfinder"><strong>Dónde pulsar en aiku</strong>
<ul>
<li><b>Tu pantalla:</b> tu organización → <b>Factory</b> → <b>Jobs</b>.</li>
<li><b>Empezar:</b> toca la fila → <b>START</b>.</li>
<li><b>Terminar:</b> escribe <b>Quantity made</b> → <b>DONE</b>.</li>
<li><b>Menos de lo pedido:</b> <b>DONE</b> → <b>Pause</b>, <b>Close at … and move remaining …</b> o <b>Job finished</b>.</li>
<li><b>Un lote para varias líneas:</b> toca una línea → <b>Combine … with other lines into one batch</b> → marca las líneas → <b>Combine</b>. Para deshacer: toca la fila combinada → <b>Separate</b>.</li>
</ul>
</aside>

<aside class="wayfinder"><strong>Permisos que necesitas</strong>
<ul>
<li>Los puestos se asignan en la ficha del empleado en Human Resources y llevan los permisos consigo.</li>
<li>Ver tus propios trabajos, START y DONE: el puesto <b>Operative</b> para la fábrica.</li>
<li>Ver y coger <b>Open jobs</b>, registrar rechazos: <b>Foreman</b>, <b>Mix preparer</b> o <b>Floor supervisor</b>.</li>
<li>Combinar y separar líneas: los responsables que dirigen la planta, las mismas personas que pueden autorizar la sobreproducción.</li>
</ul>
</aside>

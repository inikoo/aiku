---
title: Planificar el día de producción
summary: Para el responsable de producción y el supervisor de planta - poner trabajos en el plan de un día sin nombrar a nadie, dar cada paso a un operario, y qué pasa con el trabajo que no se terminó en su día.
date: 2026-10-12
source_date: 2026-10-12
tags: production, planning, floor
category: production
series: Ordering from partners
order: 13
---

<aside class="tldr">
Dos personas, dos tareas. El <b>responsable</b> decide qué se fabrica un día: en el tablero <b>To produce</b> (Por producir) elige las tarjetas y pulsa <b>Plan for this day</b> (Planificar para este día), sin nombrar a nadie. El <b>supervisor</b> abre <b>Operations → Day plan</b> (Operaciones → Plan del día) y da cada paso de cada trabajo planificado a un operario. Cada operario ve entonces sus propios pasos en la pantalla de planta. Lo que no se termine en su día sigue en el plan, en ámbar, hasta que alguien lo confirme para un día nuevo.
</aside>

## El responsable planifica el día

En **Factory → To produce** (Fábrica → Por producir), el tablero funciona como siempre: las tarjetas van de **Backlog** (Pendientes) a **Preparing** (En preparación), donde se fija la cantidad, y de **Preparing** a **Assigned** (Asignados), donde se crea la orden de trabajo.

Cuando se suelta una tarjeta en **Assigned**, la ventana que pregunta *¿Quién lo hace?* empieza ahora con una fecha y un botón:

1. Elige el día. Se abre en hoy.
2. Pulsa **Plan for this day**.

Se crea una orden de trabajo para ese día sin nadie nombrado. Cada tarjeta se convierte en su propia orden de trabajo, de modo que cada una se puede ordenar y mover por separado. Varias tarjetas seleccionadas y soltadas juntas se planifican juntas, una orden de trabajo cada una.

Elegir un nombre de la lista de abajo sigue funcionando como siempre: el trabajo entero va a esa persona y no está en el plan de ningún día.

Un trabajo que ya tiene a alguien nombrado también se puede poner en un plan: pulsa el lápiz junto al nombre en su tarjeta y luego **Plan for this day**. Se quita el nombre del trabajo y el supervisor da sus pasos.

## El supervisor da los pasos

**Factory → Operations → Day plan** lista los trabajos planificados para el día, una fila por producto de cada trabajo.

| Columna | Significado |
| --- | --- |
| Order (Orden) | el lugar del trabajo en el día - escribe un número para moverlo |
| Job (Trabajo) | la orden de trabajo y su estado |
| Product (Producto) | lo que se fabrica |
| Qty (Cant.) | cuántos |
| Steps and operators (Pasos y operarios) | cada paso de la receta: hecho hasta ahora / pedido, y a quién se le da |
| Planned (Planificado) | el día para el que se planificó |

Cada paso tiene su propia lista de nombres. Elige uno y ese paso aparece en la pantalla de planta de esa persona, y en la de nadie más. Un paso con borde ámbar aún no tiene a nadie; la barra de arriba los cuenta, *3 pasos sin operario*, para que no se quede nada olvidado por error.

Para cambiar quién hace un paso durante el día, elige otro nombre. Para quitarle un paso a alguien, pulsa la crucecita de la lista. Si la persona ya había empezado, el trabajo que hizo queda registrado a su nombre: la lista solo decide quién ve el paso a partir de ahora.

La fecha de arriba cambia a otro día. El plan de mañana se puede repartir hoy.

## Seguir el día

La misma página muestra cómo va el día y se actualiza sola:

- la barra de arriba: cuántos trabajos, *12 de 18 pasos hechos*, pasos sin operario, trabajos no terminados en su día;
- junto a cada paso: un círculo gris (sin empezar), un indicador ámbar girando (empezado) o una marca verde (hecho), con la cuenta hecha hasta ahora;
- *trabajando ahora* y el nombre, cuando alguien tiene el paso abierto en la planta.

Quien puede ver la página pero no dirige la planta ve los nombres, no las listas.

## Cuando el supervisor no está

El responsable abre la misma página y da los pasos él mismo. Planificar y dar pasos requieren el mismo puesto.

## Lo que no se terminó

Un trabajo planificado con pasos aún abiertos después de su día no se descarta. Desde el día siguiente aparece en el Day plan con su fecha antigua en ámbar, y la barra lo cuenta como *no terminado en su día*. Para cada uno, elige:

- **Confirm for** (Confirmar para) el día que estás mirando - el trabajo pasa al plan de ese día, al final del orden;
- **Take off the plan** (Quitar del plan) - el trabajo sigue como orden de trabajo pero no está en el plan de ningún día.

Hasta que se pulse una de las dos, el trabajo sigue delante de ti cada día. Sus pasos también siguen en las pantallas de planta de los operarios a quienes se dieron, así que quien pausó una mezcla ayer puede seguir esta mañana.

Cuando un operario cierra un trabajo corto y pasa el resto a uno nuevo (ver [Trabajar la pantalla de planta](/docs/working-the-floor-screen-es)), el trabajo nuevo conserva el día planificado, el lugar en el orden y el operario de cada paso.

<aside class="wayfinder"><strong>Dónde pulsar en aiku</strong>
<ul>
<li><b>Planificar un trabajo para un día:</b> <b>Factory</b> → <b>To produce</b> → suelta la tarjeta en <b>Assigned</b> → elige el día → <b>Plan for this day</b>.</li>
<li><b>Dar un paso a un operario:</b> <b>Factory</b> → <b>Operations</b> → <b>Day plan</b> → la lista junto al paso.</li>
<li><b>Cambiar el orden del día:</b> escribe un número en <b>Order</b>.</li>
<li><b>Trabajo sin terminar de ayer:</b> <b>Confirm for</b> el día, o <b>Take off the plan</b>.</li>
<li><b>Del tablero al plan:</b> el enlace <b>Day plan</b> sobre el tablero.</li>
</ul>
</aside>

<aside class="wayfinder"><strong>Permisos que necesitas</strong>
<ul>
<li>Los puestos se asignan en la ficha del empleado en Human Resources y llevan los permisos consigo.</li>
<li>Planificar un día, dar pasos, cambiar el orden: el puesto <b>Floor supervisor</b> para la fábrica.</li>
<li>Ver el Day plan sin cambiarlo: <b>Foreman</b>.</li>
<li>Trabajar un paso que te han dado: <b>Operative</b>, en la pantalla de planta.</li>
</ul>
</aside>

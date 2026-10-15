---
title: Leer el panel de PO journey
summary: Para compradores y dirección — cómo cada pedido de compra abierto se convierte en una franja, qué significa cada color y cada fecha, de dónde vienen los plazos previstos, cómo marcar las etapas que nadie más registra, y cómo encontrar los pedidos que necesitan tu atención.
date: 2026-09-25
source_date: 2026-10-15
tags: procurement, supply-chain, agents
category: procurement
series: PO journey
order: 1
---

<aside class="tldr">
Para compradores, responsables de compras y dirección. La primera página de <b>Supply Chain</b> muestra cada pedido de compra abierto como una franja, de izquierda a derecha, desde el día en que se creó hasta el día en que sus productos están a la venta. Verde es hecho, azul es dónde está el pedido ahora, rojo es la etapa que va con retraso, con los días de retraso. Marca <b>Problems only</b> para ver solo los pedidos que necesitan a alguien. Los agentes registran su parte del trabajo tal como se explica en <a href="/docs/recording-order-progress-for-agents-es">cómo registrar el progreso de un pedido como agente</a>.
</aside>

<figure><img src="/art/docs/draw-po-journey.svg" alt="Watercolor sketch of three purchase order ribbons: the first all green to the end, the second green then blue partway, the third green then red at one stage with plus twelve days, and grey planned dates after it" width="1200" height="750" loading="lazy"><figcaption>Un pedido, una franja. La celda roja es donde está atascado.</figcaption></figure>

## Qué hay en el panel

Todo pedido que sigue en camino aparece en el panel:

- **Pedidos a proveedores directos.**
- **Pedidos entre empresas AW**, por ejemplo una empresa que compra a la fábrica.
- **Pedidos a través de agentes.** Un pedido a través de un agente se hace para un proveedor que está detrás del agente, porque es ahí donde realmente ocurren la producción, los controles de calidad y los retrasos. Su fila muestra al agente y al proveedor.

Un pedido sale del panel en cuanto su mercancía se coloca en el almacén y todos sus productos están a la venta. Los pedidos terminados en los últimos 60 días siguen visibles como **Completed**, para que puedas ver lo que ha entrado. Los pedidos de más de un año que nunca se terminaron se apartan y no se muestran.

## Las etapas

El panel usa las mismas siete etapas que la página del pedido de compra, más una última columna para la publicación de los productos.

| Etapa | Completado cuando |
|---|---|
| PO submitted (pedido enviado) | El pedido se envía al proveedor o al agente. Un borrador no cuenta como enviado. |
| Deposit paid (depósito pagado) | El proveedor tiene el depósito. |
| In production (en producción) | El proveedor empieza a fabricar la mercancía. La muestra se aprueba antes, en los productos nuevos. |
| Ready & QC passed (listo y control de calidad superado) | La mercancía está fabricada y el control de calidad está aprobado. |
| Shipped (enviado) | La mercancía se entrega completa y comprobada, y sale del proveedor. |
| Arrived & cleared (llegado y despachado) | Todos los envíos del pedido han llegado al puerto y han pasado la aduana. |
| Received at warehouse (recibido en almacén) | Todos los envíos del pedido están recibidos en nuestro almacén. |
| Products online (productos publicados) | No es una etapa: todos los productos del pedido se crean y se ponen a la venta en la web. |

No todos los pedidos pasan por todas las etapas. Los pedidos entre empresas AW van directos de PO submitted a Shipped. Los pedidos a proveedores directos muestran un depósito solo cuando hay uno fijado, y la producción solo cuando el proveedor indica días de producción. Una columna que un pedido no usa se muestra como una línea fina. La aprobación de la muestra y la entrega limpia son pasos dentro de su etapa: se ven en la página del pedido, no como columnas aquí.

## Cómo leer los colores

- **Verde con una fecha:** hecho ese día.
- **Marca verde pálido:** hecho, pero nadie registró la fecha. Como ocurrió una etapa posterior, esta también tuvo que ocurrir.
- **Azul:** la etapa en la que está el pedido ahora, y va a tiempo. La fecha es su objetivo.
- **Ámbar:** la etapa actual vence dentro de tres días.
- **Rojo con +N días:** la etapa actual lleva ese número de días de retraso. Nunca hay más de una celda roja por pedido: la etapa en la que está atascado.
- **Fecha en gris:** la previsión para una etapa posterior. **Tachada** significa que esa fecha prevista ya pasó mientras el pedido sigue atascado antes, así que también llegará tarde a esa etapa.
- **En blanco:** una etapa que todavía nadie ha registrado en este pedido. Ver más abajo.

La columna de estado a la derecha dice lo mismo con palabras: *On track* con la fecha de finalización prevista, o *Overdue* con aquello que el pedido está esperando, por ejemplo *Waiting for dispatch* o *Not sent yet*.

## De dónde vienen los plazos

Cada etapa recibe un número de días de plazo a partir de cuándo terminó realmente la etapa anterior. Así que si la producción termina con dos semanas de retraso, el control de calidad se mide desde el día en que la producción realmente terminó, y el rojo aparece en la etapa que se retrasó, no en todas las que vienen después.

El número de días viene, en este orden:

1. **Fechas acordadas en el pedido.** Una fecha estimada de producción, una fecha de llegada en el pedido o en su envío, o la fecha de listo aprobada del agente. Cuando se conoce una fecha de llegada, el envío debe salir el tiempo de tránsito antes de esa fecha. Cuando se acuerda una fecha de listo, el control de calidad debe completarse ese mismo día y la mercancía debe enviarse, tras la entrega limpia, dentro de los siete días siguientes, tal como establece el acuerdo con el agente.
2. **Los días por etapa propios del agente**, si están configurados en la página del agente.
3. **El tiempo de entrega del agente o del proveedor**, repartido entre las etapas. El tiempo de entrega de cada agente se fija según lo rápido que llegó el 80% de sus pedidos anteriores, así que el rojo significa más lento de lo habitual para ese agente.

## Las etapas que nadie más registra

El sistema ve por sí solo cuándo se envía un pedido, cuándo la mercancía sale y se recibe, cuándo se coloca, y cuándo los productos se publican. La llegada al puerto se introduce en el panel de aduanas del envío. No puede ver cuándo se paga un depósito, empieza la producción o la mercancía supera el control de calidad. Eso lo tiene que marcar alguien.

- **Los agentes** marcan el depósito pagado y la producción iniciada en sus propios pedidos.
- **Los compradores** pueden marcar todas las etapas, y solo ellos aprueban la muestra y el control de calidad.

Hasta que se marque alguna de estas en un pedido, sus celdas quedan en blanco y el pedido solo se sigue por el envío y la llegada. En cuanto se marca una, el pedido queda a la espera de la siguiente etapa sin marcar, así que empieza por la primera y sigue en orden.

Para marcar una etapa, haz clic en su celda, comprueba la fecha y pulsa **Mark done**. **Clear** elimina una fecha introducida por error. Las etapas van en orden: una fecha de más de un día en el pasado, una etapa marcada antes de la anterior o borrar una fecha pide un motivo, que queda guardado en el historial del pedido junto con quién hizo el cambio.

## Filtros y cifras

Arriba del todo: **AW company**, **Journey** (agente, proveedor directo, entre empresas), **PO type** (*NPO* es un pedido con al menos un producto que nunca hemos recibido antes; todo lo demás es un pedido repetido) y **Status**. Debajo: **Agent**, **PO creator / buyer**, **Supplier**, **Country** y **Current stage**, además de **Problems only** y un buscador para el pedido, proveedor, agente o comprador.

Las tarjetas cuentan los pedidos del filtro actual: pedidos abiertos, su valor en libras al tipo de cambio de hoy, a tiempo, en riesgo, con retraso, y completados en los últimos 60 días. Haz clic en una tarjeta para filtrar por ella. **Urgent blockages**, a la derecha, agrupa los pedidos con retraso según qué están esperando; haz clic en uno para verlos.

<aside class="wayfinder">
<b>Dónde pulsar en aiku</b><br>
<b>Supply Chain</b> en el menú de la izquierda abre el panel. <b>PO journey</b> en el menú superior te devuelve a él; <b>Overview</b>, al lado, tiene las tarjetas de supply chain y las listas de la compra. Haz clic en una celda para marcar una etapa; una fecha de más de un día en el pasado, una etapa marcada antes de la anterior o borrar una fecha pide un motivo, que queda guardado en el historial del pedido. Haz clic en la referencia del pedido para abrirlo. Días por etapa de cada agente: <b>Supply Chain → Agents →</b> el agente <b>→ Edit</b>, sección <b>PO journey</b>.
</aside>

<aside class="wayfinder">
<b>Permisos que necesitas</b><br>
Permiso de visualización de supply chain para ver el panel. Permiso de edición de supply chain, o permiso de edición de compras de la empresa que creó el pedido, para marcar etapas. El control de calidad y la entrega limpia en los pedidos de los agentes los marca únicamente el personal de AW.
</aside>

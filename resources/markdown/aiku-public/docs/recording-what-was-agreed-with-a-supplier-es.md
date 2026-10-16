---
title: Registrar lo acordado con un proveedor
summary: Anota las condiciones acordadas con un proveedor o agente - Incoterm, cargos, moneda, plazo de entrega - con su prueba, y añade notas y avisos, para que se vean en cada orden de compra y no se pierdan al cambiar de comprador.
date: 2026-10-16
source_date: 2026-10-16
tags: procurement, suppliers, agents, purchase orders, agreed terms, notes, incidents
category: procurement
help_routes: grp.org.procurement.org_suppliers, grp.org.procurement.org_agents, grp.org.procurement.purchase_orders, grp.org.procurement.supplier_incidents, grp.org.procurement.stock_deliveries
---

<aside class="tldr">
Lo que acuerdas con un proveedor se registra de dos formas. Las <b>Agreed terms</b> (condiciones acordadas) son los puntos comerciales: el Incoterm, qué incluye el precio, qué cargos se permiten y por qué importe, la moneda, las condiciones de pago, el plazo de entrega. Las <b>Notes</b> (notas) son todo lo demás que un comprador debe saber: un aviso, una especificación, una instrucción para el agente, un sobrecoste que ocurrió. Ambas se introducen una sola vez en el proveedor, y a partir de ahí aiku las muestra en el proveedor, en sus productos, en el agente y en cada orden de compra, e imprime las que elijas en la orden que se envía al proveedor.
</aside>

## Dónde está

Abre el proveedor en **Procurement → Suppliers**. La primera pestaña se abre con un panel llamado **What was agreed**: las condiciones vigentes, las notas fijadas o críticas y el número de incidencias abiertas. Otras dos pestañas contienen el detalle:

- **Agreed terms** - todas las condiciones, primero las vigentes y después las que se sustituyeron o se retiraron.
- **Notes** - todas las notas escritas sobre el proveedor y sobre sus productos.

El mismo panel y las mismas dos pestañas están en cada **producto del proveedor** y en cada **agente**. En un agente, el panel incluye además **Suppliers to watch**: los proveedores del agente que tienen un aviso o una incidencia abierta.

## Añadir una condición acordada

En la pestaña **Agreed terms** pulsa **Add agreed term** y rellena:

- **Term** - de qué trata la condición: Incoterm, Packing charge, Label charge, Barcode charge, Carton marking charge, Insert or leaflet charge, Mould or tooling charge, Sample charge, Local delivery to port charge, Payment currency, Payment terms, Lead time in days, Minimum order u Other charge.
- **What was agreed** - **Included** (está en el precio), **Not allowed** (el proveedor no puede cobrarlo), un importe (**Fixed amount**, **Per unit**, **Per carton**, **Per order**, **Percentage**) o **Text**.
- **Amount**, **Currency** y **Tolerance** - aparecen cuando eliges un importe. La tolerancia es la diferencia que aceptas antes de que cuente como desviación.
- La línea de texto - el Incoterm y su lugar (por ejemplo FOB Ningbo), para qué sirve un Other charge, o cualquier detalle.
- **Agreed on** y **Agreed with** - la fecha y el contacto en el proveedor o agente.
- **Evidence** - el correo, el chat o el documento donde se acordó.
- **Who may see it** - **Internal**, **Agent** o **Supplier**.
- **Print on the purchase order** - actívalo para que la condición se imprima en la orden.

Un cargo con importe no se puede guardar sin prueba. aiku te registra como la persona que acordó la condición.

<aside class="tip">
Para que una condición aparezca en la orden de compra se necesitan las dos cosas: <b>Print on the purchase order</b> activado y <b>Who may see it</b> en Supplier o Agent. Una condición interna nunca se imprime.
</aside>

## Una organización o todas

Un proveedor lo comparten todas las organizaciones que le compran, y también sus condiciones: lo que registra un comprador rige para todas. Cuando tu organización tiene un acuerdo distinto, activa **Only for this organisation**. Entonces tu organización ve su propia condición y las demás conservan la compartida.

## Una condición para un solo producto

Una condición introducida en un **producto del proveedor** sustituye la condición del proveedor del mismo tipo, solo para ese producto. La página del producto muestra de dónde viene cada condición: **This product**, **Supplier** o **Agent**. En la pestaña **Agreed terms** del producto, la condición del proveedor a la que sustituye aparece marcada como **Overridden**.

## Cambiar o retirar una condición

Las condiciones nunca se editan ni se borran. Pulsa **Change** en una condición, introduce lo que se acuerda ahora y guarda: la nueva condición pasa a estar vigente y la anterior queda en la lista como **Superseded**, con la fecha en que dejó de regir. Pulsa **Withdraw** cuando una condición ya no se aplica y nada la sustituye.

Una condición la pueden cambiar o retirar quien la acordó y el supervisor de compras.

## Notas

En la pestaña **Notes** pulsa **Add note**. Escribe una línea de título y, si hace falta, el detalle debajo. Después elige:

- la **categoría**, que da su color a la nota;
- la **gravedad**: Info, Warning o Critical;
- quién puede verla: Internal, Agent o Supplier. Una nota nueva es interna;
- **Pin: show on purchase orders**, para una nota que todo comprador debe ver al hacer un pedido.

| Categoría | Color | Úsala para |
| --- | --- | --- |
| Agent | Morado | Instrucciones para el agente sobre este proveedor |
| Product | Azul | Especificación acordada, material, tamaño, acabado |
| Packaging | Verde | Embalaje interior y exterior, etiquetas, insertos |
| Charges | Rojo | Cargos acordados o en disputa |
| Delivery | Naranja | Plazos de entrega, retrasos de temporada, envío |
| Currency and payment | Amarillo | Moneda, depósito, forma de pago |
| Incident | Negro | Un sobrecoste o una entrega errónea que ocurrió |
| General | Gris | Cualquier otra cosa, y las notas escritas antes de que existieran las categorías |

En una **Incident** puedes indicar el importe sobrecobrado o reclamado y su moneda. Una incidencia empieza abierta y cuenta en el panel hasta que se resuelve.

Las notas tampoco se borran. Pulsa **Resolve** cuando una nota ya no se aplica; se queda en la lista, tachada. **Pin** y **Unpin** meten y sacan una nota del panel. El autor de una nota y el supervisor de compras pueden fijarla y resolverla.

## En una orden de compra

Abre una orden de compra del proveedor. Encima de las líneas, el panel **What was agreed** muestra las condiciones vigentes del proveedor y de los productos de la orden, y las notas fijadas o críticas de la orden, del proveedor, de su agente y de esos productos.

Cuando una de esas notas es **Critical**, marca **I have read the critical notes for this order** antes de pulsar **Submit**. Sin la marca, la orden no se envía.

La pestaña **Notes** de una orden de compra y de una entrega de stock tiene el mismo formulario que la del proveedor, así que una incidencia se puede escribir en la orden donde ocurrió; queda entonces vinculada a esa orden.

## Qué ven el proveedor y el agente

El PDF de la orden de compra lleva un bloque de **Agreed terms** y otro de **Notes**, y el correo que lo envía enumera las condiciones acordadas. Contienen solo lo marcado para que lo vea el proveedor y, en una orden hecha a través de un agente, el agente. Las condiciones y notas internas nunca se imprimen.

Un agente que trabaja en aiku ve, en sus propias páginas, solo las condiciones y notas marcadas como visibles para el agente, y no puede añadir ni cambiar ninguna.

## Traspasar un proveedor

En el proveedor, pulsa **Handover sheet** en el panel **What was agreed**. Se abre un PDF con todas las condiciones vigentes y todas las notas abiertas, para dárselo a la persona que se hace cargo del proveedor.

## Cuando un pedido o una factura no coincide con lo acordado

aiku compara lo pedido y lo facturado con las condiciones acordadas, en cada proveedor o agente con al menos una condición acordada registrada. Un proveedor o un agente no puede subir un precio ni añadir un cargo por su cuenta: cada diferencia espera a que compras la acepte.

- **En una orden de compra:** una línea que cuesta más que el coste del producto del proveedor, o un pedido en otra moneda que la moneda de pago acordada.
- **En la factura del proveedor o del agente de una entrega:** un cargo cuya condición es Incluido o No permitido, un cargo por encima del importe acordado, un cargo sin condición acordada, o el flete cuando el Incoterm acordado (CFR, CIF, CPT, CIP, DAP, DPU, DDP) hace que lo pague el proveedor.

Al introducir la factura, indica el tipo de cada cargo (cargo de embalaje, cargo de etiquetas, flete, etc.) para que se compare con la condición correcta.

Cada diferencia aparece en rojo en el panel **Diferente de lo acordado**, arriba de la orden o de la entrega, junto a lo acordado. Escribe por qué la aceptas y pulsa **Aceptar**. Hasta que se aceptan todas, la orden no se puede enviar, la entrega no se puede pagar y los cargos del agente no se pueden aprobar. Si la orden o la factura cambia después, la nueva diferencia se tiene que aceptar otra vez.

Cada diferencia aceptada queda como una nota de **Incidencia** abierta en el proveedor o el agente, con el importe y tu motivo, y se avisa al supervisor de compras y al comprador de la orden. Las listas de proveedores muestran el número de incidencias abiertas junto al código del proveedor.

## Comprobar una entrega al llegar

Desde el momento en que se despacha una entrega de stock, tiene una pestaña **Checklist** (lista de comprobación), en la página de compras de la entrega y en la página de entrada de mercancía del almacén. Enumera, por cada proveedor de la entrega y después por cada uno de sus productos:

- **Packing** (Embalaje) - quién empaqueta la mercancía y en qué material, cuando está registrado en el proveedor o en el producto;
- cada nota abierta de la categoría **Packaging** (Embalaje) o **Product** (Producto) escrita en ese proveedor o producto.

El número de la pestaña es cuántas comprobaciones quedan por hacer. Pulsa **Passed** (Superado) cuando la mercancía coincide. Pulsa **Failed** (Fallido) cuando no coincide: elige el tipo de problema, indica la cantidad afectada si la sabes, escribe qué está mal y pulsa **Open incident** (Abrir incidencia). aiku abre una nota de **Incident** en ese proveedor o producto, vinculada a la orden de compra con la que llegó la mercancía, y la comprobación muestra quién la marcó como fallida y cuándo.

El personal de entrada de mercancía puede marcar la lista de comprobación y puede escribir una **Incident** en la pestaña **Notes** de una entrega de stock. No puede escribir notas de ninguna otra categoría.

## Entregas con retraso

Cuando se recibe una entrega, aiku mide cada una de sus órdenes de compra desde el día en que se envió hasta el día en que se recibió la entrega, y lo compara con el **Lead time in days** acordado con el proveedor, o con su agente. Una orden con más de 7 días de retraso sobre lo acordado recibe en el proveedor una **Incident** de tipo **Late** (Con retraso), vinculada a la orden, que indica cuántos días tardó. Se escribe una sola vez por orden. Un proveedor sin plazo de entrega acordado no se mide.

## Informe de incidencias de proveedores

La tarjeta **Supplier incidents** (Incidencias de proveedores) del panel de compras, que cuenta las incidencias abiertas, abre un informe que enumera cada proveedor o agente con incidencias en tu organización: cuántas, cuántas siguen abiertas, el importe en disputa y el importe recuperado, con la fecha de la última. Una incidencia en un producto del proveedor o en una orden de compra cuenta para su proveedor. Los importes están en la moneda del grupo, convertidos el día en que se escribió la incidencia. **Recovered** (Recuperado) es el importe de las incidencias cuyo resultado se fijó en **Credited** (Abonado). Pulsa el código del proveedor para abrir sus notas.

<aside class="wayfinder"><strong>Dónde pulsar en aiku</strong>
<ul>
<li><b>Ver lo acordado con un proveedor:</b> tu organización → <b>Procurement → Suppliers</b> → el proveedor → el panel <b>What was agreed</b>.</li>
<li><b>Añadir una condición:</b> el proveedor → <b>Agreed terms</b> → <b>Add agreed term</b>.</li>
<li><b>Cambiar o retirar una condición:</b> el proveedor → <b>Agreed terms</b> → <b>Change</b> o <b>Withdraw</b> en su fila.</li>
<li><b>Añadir una nota:</b> el proveedor → <b>Notes</b> → <b>Add note</b>.</li>
<li><b>Fijar o resolver una nota:</b> el proveedor → <b>Notes</b> → <b>Pin</b> o <b>Resolve</b> en su fila.</li>
<li><b>Una condición o una nota para un solo producto:</b> el proveedor → sus productos → el producto → <b>Agreed terms</b> o <b>Notes</b>.</li>
<li><b>Confirmar la lectura de las notas críticas:</b> la orden de compra → marca <b>I have read the critical notes for this order</b> → <b>Submit</b>.</li>
<li><b>Imprimir la hoja de traspaso:</b> el proveedor → panel <b>What was agreed</b> → <b>Handover sheet</b>.</li>
<li><b>Comprobar una entrega:</b> la entrega de stock → <b>Checklist</b> → <b>Passed</b> o <b>Failed</b> en cada línea.</li>
<li><b>Ver incidencias por proveedor:</b> panel de <b>Procurement</b> → la tarjeta <b>Supplier incidents</b>.</li>
<li><b>Aceptar una diferencia:</b> la orden de compra o la entrega → <b>Diferente de lo acordado</b> → escribe el motivo → <b>Aceptar</b>.</li>
</ul>
</aside>

<aside class="permissions"><strong>Permisos que necesitas</strong>
Necesitas permiso para ver compras (procurement) de la organización para ver condiciones y notas, y permiso para editar compras para añadir una nota o una condición. Cambiar o retirar una condición acordada por otra persona, y fijar o resolver una nota escrita por otra persona, requieren el puesto de supervisor de compras. Aceptar una diferencia con lo acordado requiere permiso para editar compras en la organización que paga. El personal de entrada de mercancía con permiso para editar entrada de mercancía en uno de los almacenes de la organización puede marcar la lista de comprobación de la entrega y registrar incidencias.
</aside>

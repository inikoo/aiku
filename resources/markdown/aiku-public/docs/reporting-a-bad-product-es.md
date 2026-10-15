---
title: Informar de un producto malo y leer las pruebas
summary: Cómo compras, entrada de mercancía y atención al cliente señalan un producto que debería retirarse, y cómo los Supply Chain Managers leen esos informes, junto a los reembolsos y reposiciones que aiku cuenta por sí solo, en la página Goods.
date: 2026-10-15
source_date: 2026-10-15
tags: procurement, goods, discontinue, bad feedback, complaints, refunds, replacements, supply chain
category: procurement
---

<aside class="tldr">
Cualquiera que se encuentre con un producto que da problemas de forma repetida puede decirlo con dos clics: <b>Suggest discontinuing</b> (sugerir descatalogar) al comprar, <b>Report a bad product</b> (informar de un producto malo) en la entrada de mercancía, <b>Bad product</b> (producto malo) en un chat con un cliente. Un informe no cambia nada en el producto. Es un voto, guardado con quién lo envió, desde dónde y por qué. Los Supply Chain Managers ven los votos en la página <b>Goods</b>, junto a los comentarios negativos que aiku cuenta por sí solo a partir de reembolsos, reposiciones y reclamaciones al proveedor, y allí deciden si cambia el estado del producto.
</aside>

## Informar de un producto malo

El botón está en tres sitios, uno para cada equipo que se encuentra con el producto:

- **Buying** (compras) - **Suggest discontinuing**, bajo la información de stock de cada línea de una orden de compra en borrador (pestaña **Items**), en la pestaña **All supplier's products** de una orden de compra y en la página **Ordering** de un agente. No se muestra cuando pides a una organización socia.
- **Goods in** (entrada de mercancía) - **Report a bad product**, en cada línea de una entrega de stock.
- **Customer services** (atención al cliente) - **Bad product**, junto a cada línea del panel de reclamaciones en un chat con un cliente.

Cada uno abre el mismo cuadro de diálogo. Marca al menos un motivo:

- High complaints (muchas quejas)
- Slow seller (se vende despacio)
- High competition (mucha competencia)
- Import issues (problemas de importación)
- Arrived broken (llegó roto)
- Arrived rotten or out of date (llegó podrido o caducado)

Después escribe, con tus palabras, lo que has visto con el producto. Ambas cosas son obligatorias: **Send suggestion** (enviar sugerencia) sigue en gris hasta que marcas un motivo y hay un texto.

Al enviar no cambia nada en el producto. Tu informe se suma al recuento, y los Supply Chain Managers actúan cuando suficientes personas señalan el mismo producto. Puedes volver a informar del mismo producto más adelante, por ejemplo cuando llegue otra entrega rota: cada informe cuenta.

En las líneas de compra, cuando un producto ya ha sido señalado, aparece una etiqueta ámbar con **Suggested N times by M people** (sugerido N veces por M personas). El recuento es de todo el grupo, no solo de tu organización.

## Leer las pruebas en la página Goods

La página **Goods**, en el menú del grupo, lista todos los productos del grupo. Dos cosas en ella proceden de los informes de producto malo.

### La tarjeta Suggested to discontinue

La última tarjeta de la parte superior, **Suggested to discontinue** (sugeridos para descatalogar), muestra cuántos productos ha señalado el personal y, debajo, cuántos informes se han enviado en total. Haz clic en la tarjeta para listar solo esos productos, ordenados del más señalado al menos. Vuelve a hacer clic para volver a ver todos los productos.

### La columna Bad feedback

La columna **Bad feedback** (comentarios negativos) tiene hasta dos cápsulas en cada fila.

La primera es lo que aiku cuenta **por sí solo**, en los últimos 12 meses, sin que nadie tenga que informar:

- reembolsos de mercancía que se envió;
- reposiciones enviadas, salvo cuando la culpa fue del transportista, del almacén o del cliente (dañado o perdido en el transporte, artículo equivocado, falta en el paquete, error del cliente);
- artículos añadidos gratis al siguiente pedido del cliente;
- reclamaciones al proveedor presentadas en la entrada de mercancía.

La cápsula muestra un nivel, el porcentaje de pedidos que acabaron con el cliente compensado, y el número de clientes compensados frente al número de pedidos que llevaban el producto. Pasa el cursor por las cifras para ver el desglose en reembolsos, reposiciones, artículos enviados en un pedido posterior y reclamaciones al proveedor. Un producto con reclamaciones al proveedor pero sin ningún cliente compensado muestra el número de reclamaciones al proveedor.

El nivel compara los clientes compensados con la frecuencia con la que se pide el producto, de modo que un superventas con unos pocos reembolsos no se marca como malo igual que un producto que se reembolsa en la mayoría de sus pocos pedidos:

| Level | Porcentaje de pedidos con un cliente compensado |
| --- | --- |
| Mild | menos del 2 %, o menos de 3 clientes compensados |
| Bad | desde el 2 % |
| Severe | desde el 5 % |
| Disaster | desde el 10 % |

Un producto necesita al menos 3 clientes compensados para puntuar por encima de Mild: por debajo de eso, un solo reembolso en un único pedido se leería como un 100 %.

La segunda cápsula son los **votos del personal**: el número de personas que han señalado el producto. Es ámbar y pasa a rojo a partir de 3 personas. Pasa el cursor para ver el número de personas y de informes; haz clic para leer cada informe: quién lo envió, desde qué equipo y organización, cuándo, los motivos marcados y el texto. La parte superior de esa lista cuenta los informes por equipo y por motivo.

### Filtrar por nivel

Encima de la tabla, el filtro **Bad feedback** ofrece **Mild**, **Bad**, **Severe** y **Disaster**, cada uno con el número de productos de ese nivel. Elige uno para listar solo esos productos, los peores primero; elige **All** para quitar el filtro. El filtro sigue el nivel que aiku cuenta por sí solo; los votos del personal están en la tarjeta.

### Actuar a partir de ello

Las pruebas no cambian nada por sí solas. Para cambiar el estado de un producto, usa los controles que ya hay en la página: en la columna **Status** elige el nuevo estado y pulsa **Set**, o marca varias filas y usa **Change status to** (cambiar estado a) encima de la tabla. En ambos casos una vista previa muestra a qué afecta el cambio, y no cambia nada hasta que confirmes.

## El resto de la lista de productos

La lista de productos de Goods es una tabla estándar de aiku: ordena por **Code - description**, **Sales** o **Bad feedback** haciendo clic en el encabezado, elige cuántas filas por página y oculta la columna **Bad feedback** cuando no la necesites.

- **Code - description** - haz clic para abrir el detalle del producto en un lateral.
- **Sales** - las ventas del grupo del producto en el periodo elegido encima de la tabla (Last 30 days, Last 90 days, This quarter, Last 12 months), en miles, con la tendencia frente al periodo anterior: verde si sube, rojo si baja. Después sigue un punto por organización: verde si sube, rojo si baja, gris oscuro si se vendió sin nada con lo que comparar, gris claro si no hay ventas, un círculo vacío cuando la organización no lo tiene en stock. Pasa el cursor para ver las ventas y la tendencia de cada organización.
- **Group stock** - lo que hay disponible en todas las organizaciones juntas y, en verde, lo que viene de camino. Después sigue un punto por organización, coloreado según su situación de stock. Pasa el cursor para ver, por organización: disponible, de camino con la próxima fecha prevista y su cobertura en semanas.
- **Bad feedback** - ver arriba.
- **Status** - un icono: marca verde Active, pausa gris Hold, cruz ámbar Discontinued, cruz roja Retired. Pasa el cursor para ver el nombre.

## Qué no cubre

- Un producto que no está vinculado a un SKO de grupo (master stock) sí se puede señalar, pero no aparece en la página Goods, así que sus votos no se ven allí.
- Un producto formado por varios SKO cuenta contra cada uno de ellos: un reembolso de ese producto suma a los comentarios negativos de cada SKO que lo compone, y un informe también.

<aside class="wayfinder"><strong>Dónde hacer clic en aiku</strong>
<ul>
<li><b>Informar de un producto al comprar:</b> la orden de compra en borrador → <b>Items</b> o <b>All supplier's products</b> → <b>Suggest discontinuing</b> bajo la información de stock de la línea.</li>
<li><b>Informar de un producto en la entrada de mercancía:</b> la entrega de stock → <b>Report a bad product</b> en la línea.</li>
<li><b>Informar de un producto desde un chat:</b> el chat con el cliente → el panel de reclamaciones → <b>Bad product</b> junto a la línea.</li>
<li><b>Ver los productos señalados:</b> menú del grupo → <b>Goods</b> → la tarjeta <b>Suggested to discontinue</b>.</li>
<li><b>Leer los informes de un producto:</b> <b>Goods</b> → columna <b>Bad feedback</b> → clic en la cápsula de votos.</li>
<li><b>Listar productos por nivel:</b> <b>Goods</b> → el filtro <b>Bad feedback</b> encima de la tabla → el nivel.</li>
<li><b>Cambiar el estado de un producto:</b> <b>Goods</b> → columna <b>Status</b> → elige el estado → <b>Set</b>.</li>
</ul>
</aside>

<aside class="permissions"><strong>Permisos que necesitas</strong>
Para enviar un informe necesitas, en la organización de la línea, permiso para editar compras (procurement), permiso para dar entrada a mercancía en uno de sus almacenes o permiso para editar clientes en una de sus tiendas. La página Goods necesita permiso para ver Goods, y cambiar allí el estado de un producto requiere el permiso de cadena de suministro (supply chain) del grupo.
</aside>

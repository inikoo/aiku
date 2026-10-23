---
title: Qué pasa con tus descripciones de producto
summary: Qué textos, imágenes y precios escribimos en Shopify cuando creas o vinculas un producto, qué mantenemos actualizado después, y qué envía Edit Price.
date: 2026-10-23
source_date: 2026-09-25
tags: shopify, descripción de producto, título, seo, vincular, crear nuevo producto, precio
category: products
series: shopify
order: 5
shops: awd, dssk, dse
---

<aside class="tldr">
<b>Crear nuevo producto</b> crea un producto nuevo en Shopify con nuestro título, descripción e imágenes. <b>Match</b> vincula un producto que ya tienes y mantiene tu título, descripción, imágenes y SEO. Después de eso solo mantenemos el stock actualizado, así que los cambios posteriores en nuestras descripciones no llegan a Shopify. <b>Edit Price</b> con <b>Guardar y publicar</b> envía solo el precio, nunca el título ni la descripción.
</aside>

## Cuando pulsas Create new product

Creamos un producto nuevo en tu tienda de Shopify con:

- nuestro nombre de producto como título, y nuestra descripción, con enlaces a los documentos del producto cuando los hay;
- nuestras imágenes;
- nuestro nombre de familia como tipo de producto, el nombre de nuestra tienda como proveedor, y nuestras etiquetas;
- una variante con nuestro SKU, código de barras, peso, coste y tu precio de venta;
- el stock, guardado en la ubicación <b>aiku-</b>.

El precio de venta es nuestro PVPR, modificado por la <b>Pricing Policy</b> de tu canal si la configuraste antes de añadir el producto. Después puedes cambiar los textos e imágenes en Shopify. No los sobrescribimos más adelante.

## Cuando vinculas un producto que ya tienes

Vincular conecta nuestro producto con un producto que ya está en tu tienda. Consulta [Gestionar productos en Shopify](/docs/managing-products-on-shopify).

No cambiamos:

- el título, la descripción y las imágenes;
- el título SEO, la meta descripción y la URL;
- tus propios campos y apps.

Sí cambiamos la variante, porque la necesitamos para enviar stock y recibir pedidos:

- Sustituimos la única variante del producto por una con nuestro SKU, código de barras, peso y coste, y la ubicamos en <b>aiku-</b>.
- Mantenemos tu precio de venta si la variante tenía un precio superior a cero. Si no, usamos tu precio de venta de <b>Mis productos</b>.

Si vendes nuestros productos como variantes de un solo producto de Shopify, da a cada variante el SKU de su producto. Al emparejar, solo vinculamos la variante que lleva nuestro SKU y no cambiamos nada más en ella: ni el precio, ni el SKU, ni el código de barras. No hay nada que activar.

## Qué actualizamos después

Una vez conectado un producto, mantenemos su stock actualizado en Shopify mientras <b>Actualización de stock</b> esté activado para el canal.

No enviamos cambios posteriores del título, la descripción o las imágenes. Si mejoramos una descripción en nuestro catálogo, tu producto de Shopify conserva el texto anterior. Esto protege los cambios que hagas en Shopify.

El peso y las medidas se vuelven a enviar solo cuando pulsas <b>Update all dimensions</b> en <b>Mis productos</b>.

## Edit Price envía solo el precio

Cuando marcas productos en <b>Mis productos</b>, pulsas <b>Edit Price (...)</b> y luego <b>Guardar y publicar</b>, enviamos el nuevo precio a Shopify. Tu título, descripción e imágenes en Shopify se quedan como están.

<b>Guardar como borrador</b> solo guarda el precio con nosotros y no envía nada a Shopify. Un producto vinculado recibe el nuevo precio solo cuando muestra <b>Price managed by us</b>; con <b>Price managed in your Shopify</b> no se envía nada.

## Una forma segura de empezar

1. Añade unos pocos productos a <b>Mis productos</b>.
2. Si ya los vendes, vincúlalos. Si no, créalos.
3. Compruébalos en Shopify: título, descripción, imágenes, precio y stock.
4. Cuando todo esté bien, haz el resto.

## Cuando algo falla

**Mis descripciones de Shopify no se actualizan cuando cambian las mías.** Esto es normal: no enviamos cambios de descripción después de conectar un producto. Copia el texto nuevo en Shopify tú mismo.

**Tengo una copia duplicada de un producto que ya vendo.** Se creó con <b>Crear nuevo producto</b> en vez de vincularse. Elimina la copia nueva en Shopify, y luego usa <b>Conectar con otros productos</b> en <b>Mis productos</b> para vincular nuestro producto con el tuyo original. Antes de vincular, comprueba que tu producto tiene el mismo SKU que nuestro código de producto.

<aside class="wayfinder"><strong>Dónde pulsar</strong>
<ul>
<li><b>Conserva tus propios textos:</b> <b>Mis productos</b> → <b>Combina con este producto</b> o <b>Match all with default product</b>.</li>
<li><b>Usa nuestros textos e imágenes:</b> <b>Mis productos</b> → <b>Crear nuevo producto</b>.</li>
<li><b>Envía de nuevo pesos y medidas:</b> <b>Mis productos</b> → <b>Update all dimensions</b>.</li>
</ul>
</aside>

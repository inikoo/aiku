---
title: Ce qu'il advient de vos descriptions de produits
summary: Quels textes, images et prix nous écrivons dans Shopify quand vous créez ou faites correspondre un produit, ce que nous tenons à jour ensuite, et ce que Edit Price envoie.
date: 2026-10-23
source_date: 2026-09-25
tags: shopify, description de produit, titre, référencement, correspondance, création de produit, prix
category: products
series: shopify
order: 5
shops: awd, dssk, dse
---

<aside class="tldr">
<b>Create new product</b> crée un nouveau produit Shopify avec notre titre, notre description et nos images. <b>Match</b> lie un produit que vous avez déjà et garde votre titre, votre description, vos images et votre référencement. Ensuite, nous ne tenons à jour que le stock, donc les changements ultérieurs de nos descriptions n'atteignent pas Shopify. <b>Edit Price</b> avec <b>Save & Publish</b> n'envoie que le prix, jamais le titre ni la description.
</aside>

## Quand vous appuyez sur Create new product

Nous créons un nouveau produit dans votre boutique Shopify avec :

- notre nom de produit comme titre, et notre description, avec des liens vers les documents produit quand il y en a ;
- nos images ;
- notre nom de famille comme type de produit, notre nom de boutique comme fournisseur, et nos étiquettes ;
- une variante avec notre SKU, notre code-barres, notre poids, notre coût et votre prix de vente ;
- le stock, tenu à jour à l'emplacement <b>aiku-</b>.

Le prix de vente est notre PVC, modifié par la <b>Pricing Policy</b> de votre canal si vous en avez réglé une avant d'ajouter le produit. Vous pouvez ensuite changer les textes et les images dans Shopify. Nous ne les écrasons pas ensuite.

## Quand vous faites correspondre un produit que vous avez déjà

Faire correspondre lie notre produit à un produit déjà présent dans votre boutique. Voir [Gérer les produits sur Shopify](/docs/managing-products-on-shopify).

Nous ne changeons pas :

- le titre, la description et les images ;
- le titre SEO, la méta-description et l'URL ;
- vos propres champs et applications.

Nous changeons la variante, car nous en avons besoin pour envoyer le stock et recevoir les commandes :

- Nous remplaçons la variante unique du produit par une variante portant notre SKU, notre code-barres, notre poids et notre coût, et la stockons à l'emplacement <b>aiku-</b>.
- Nous gardons votre prix de vente si la variante avait un prix supérieur à zéro. Sinon, nous utilisons votre prix de vente issu de <b>My Products</b>.

Si vous vendez nos produits comme les variantes d'un seul produit Shopify, donnez à chaque variante le SKU de son produit. Lors de la correspondance, nous ne lions que la variante portant notre SKU et ne changeons rien d'autre dessus : ni le prix, ni le SKU, ni le code-barres. Il n'y a rien à activer.

## Ce que nous mettons à jour ensuite

Une fois un produit connecté, nous tenons son stock à jour dans Shopify, tant que <b>Stock Update</b> est activé pour le canal.

Nous n'envoyons pas les changements ultérieurs du titre, de la description ou des images. Si nous améliorons une description dans notre catalogue, votre produit Shopify garde l'ancien texte. Cela protège les changements que vous faites dans Shopify.

Le poids et la taille ne sont renvoyés que lorsque vous appuyez sur <b>Update all dimensions</b> dans <b>My Products</b>.

## Edit Price n'envoie que le prix

Quand vous cochez des produits dans <b>My Products</b>, appuyez sur <b>Edit Price (...)</b> puis sur <b>Save & Publish</b>, nous envoyons le nouveau prix à Shopify. Votre titre, votre description et vos images dans Shopify restent tels quels.

<b>Save as Draft</b> ne fait qu'enregistrer le prix chez nous et n'envoie rien à Shopify. Un produit lié ne reçoit le nouveau prix que s'il affiche <b>Price managed by us</b> ; avec <b>Price managed in your Shopify</b>, rien n'est envoyé.

## Une façon prudente de commencer

1. Ajoutez quelques produits à <b>My Products</b>.
2. Si vous les vendez déjà, faites-les correspondre. Sinon, créez-les.
3. Vérifiez-les dans Shopify : titre, description, images, prix et stock.
4. Quand tout semble correct, faites le reste.

## En cas de problème

**Mes descriptions Shopify ne se mettent pas à jour quand les vôtres changent.** C'est normal : nous n'envoyons pas les changements de description après qu'un produit est connecté. Copiez le nouveau texte dans Shopify vous-même.

**J'ai obtenu un second exemplaire d'un produit que je vends déjà.** Il a été créé avec <b>Create new product</b> au lieu d'être lié. Supprimez le nouvel exemplaire dans Shopify, puis utilisez <b>Connect with other product</b> dans <b>My Products</b> pour lier notre produit à votre produit d'origine. Avant de faire correspondre, vérifiez que votre produit a le même SKU que notre code produit.

<aside class="wayfinder"><strong>Où cliquer</strong>
<ul>
<li><b>Garder vos propres textes :</b> <b>My Products</b> → <b>Match with this product</b> ou <b>Match all with default product</b>.</li>
<li><b>Utiliser nos textes et images :</b> <b>My Products</b> → <b>Create new product</b>.</li>
<li><b>Renvoyer les poids et tailles :</b> <b>My Products</b> → <b>Update all dimensions</b>.</li>
</ul>
</aside>

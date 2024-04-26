

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 25 Apr 2024 15:15:47 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Product} from "@/types/product";
import Icon from "@/Components/Icon.vue";
import {library} from "@fortawesome/fontawesome-svg-core";
import {faConciergeBell, faGarage} from '@fal'
import product from "@/Pages/Grp/Market/Product.vue";

library.add(faConciergeBell, faGarage)


const props = defineProps<{
  data: object
  tab?: string,
}>()


function rentalRoute(product: Product) {
  console.log(route().current())
  switch (route().current()) {

    case "grp.org.shops.show.catalogue.products.index":
      return route(
          'grp.org.shops.show.catalogue.products.show',
          [route().params['organisation'], route().params['shop'], product.slug]);
    case 'grp.org.shops.index':
      return route(
          'grp.org.shops.show.catalogue.products.show',
          [route().params['organisation'], product.shop_slug, product.slug]);
    case 'grp.org.fulfilments.show.products.index':
      return route(
          'grp.org.fulfilments.show.products.show',
          [route().params['organisation'], route().params['fulfilment'], product.slug]);
    default:
      return null
  }
}


</script>

<template>
  <Table :resource="data" :name="tab" class="mt-5">
    <template #cell(code)="{ item: rental }">
      <Link :href="rentalRoute(rental)" class="specialUnderline">
        {{ rental['code'] }}
      </Link>
    </template>
    <template #cell(shop_code)="{ item: rental }">
      <Link v-if="product['shop_slug']" :href="rentalRoute(rental)" class="specialUnderlineSecondary">
        {{ rental['shop_slug'] }}
      </Link>
    </template>
    <template #cell(state)="{ item: rental }">
      <Icon :data="rental['state_icon']"/>
    </template>
  </Table>
</template>



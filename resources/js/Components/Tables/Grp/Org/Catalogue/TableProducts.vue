<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Product} from "@/types/product";
import Icon from "@/Components/Icon.vue";
import {library} from "@fortawesome/fontawesome-svg-core";
import {faConciergeBell, faGarage} from '@fal'

library.add(faConciergeBell, faGarage)


const props = defineProps<{
  data: object
  tab?: string,
}>()

function productRoute(product: Product) {

  switch (route().current()) {

    case "grp.org.shops.show.catalogue.products.index":
    case "grp.org.shops.show.catalogue.dashboard":
      return route(
          'grp.org.shops.show.catalogue.products.show',
          [route().params['organisation'], route().params['shop'], product.slug]);
    case 'grp.org.shops.index':
      return route(
          'grp.org.shops.show.catalogue.products.show',
          [route().params['organisation'], product.shop_slug, product.slug]);
    case 'grp.org.fulfilments.show.billables.index':
      return route(
          'grp.org.fulfilments.show.billables.show',
          [route().params['organisation'], route().params['fulfilment'], product.slug]);
    case 'grp.org.shops.show.catalogue.departments.show':
      return route(
          'grp.org.shops.show.catalogue.departments.show.products.show',
          [route().params['organisation'], route().params['shop'], route().params['department'], product.slug]);
    default:
      return null
  }
}


</script>

<template>
  <Table :resource="data" :name="tab" class="mt-5">
    <template #cell(code)="{ item: product }">
      <Link :href="productRoute(product)" class="primaryLink">
        {{ product['code'] }}
      </Link>
    </template>
    <template #cell(shop_code)="{ item: product }">
      <Link v-if="product['shop_slug']" :href="productRoute(product)" class="secondaryLink">
        {{ product['shop_slug'] }}
      </Link>
    </template>
    <template #cell(type)="{ item: product }">
      <Icon :data="product['type_icon']"/>
      <Icon :data="product['state_icon']"/>
    </template>
  </Table>
</template>



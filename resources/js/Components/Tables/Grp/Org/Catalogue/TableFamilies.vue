<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { Family } from "@/types/family";
import Icon from "@/Components/Icon.vue"


const props = defineProps<{
  data: object
  tab?: string,
}>();


function familyRoute(family: Family) {
  switch (route().current()) { 
    case "grp.shops.show":
    case "grp.org.shops.show.catalogue.families.index":
      return route(
        "grp.org.shops.show.catalogue.families.show",
        [route().params["organisation"], route().params["shop"], family.slug]);
    case "grp.org.shops.show.catalogue.departments.show":
      return route( 
        "grp.org.shops.show.catalogue.departments.show.families.show",
        [route().params["organisation"], route().params["shop"], route().params["department"], family.slug]);
    case 'grp.org.shops.index':
      return route(
        "grp.org.shops.show.catalogue.families.show",
        [route().params["organisation"], family.shop_slug, family.slug]);
    case "grp.org.shops.show.catalogue.dashboard":
    return route(
        "grp.org.shops.show.catalogue.families.show",
        [route().params["organisation"], route().params["shop"], family.slug]);
    case "grp.org.shops.show.catalogue.departments.show.families.index":
    return route(
        "grp.org.shops.show.catalogue.departments.show.families.show",
        [route().params["organisation"], route().params["shop"], route().params["department"], family.slug]);
  }
}

function shopRoute(family: Family) {
    switch (route().current()) {
        case 'grp.org.shops.index':
            return route(
                "grp.org.shops.show.catalogue.dashboard",
                [route().params["organisation"], family.shop_slug]);
    }
}

function departmentRoute(family: Family) {
    switch (route().current()) {
        case 'grp.org.shops.index':
            return route(
                "grp.org.shops.show.catalogue.departments.index",
                [route().params["organisation"], family.shop_slug,family.department_slug]);
        case 'grp.org.shops.show.catalogue.dashboard':
        case 'grp.org.shops.show.catalogue.families.index':
            return route(
                "grp.org.shops.show.catalogue.departments.show",
                [route().params["organisation"], route().params["shop"],family.department_slug]);

    }
}

</script>

<template>
  <Table :resource="data" :name="tab" class="mt-5">
    <template #cell(state)="{ item: family }">
      <Icon :data="family.state">
      </Icon>
    </template>
    <template #cell(code)="{ item: family }">
      <Link :href="familyRoute(family)" class="primaryLink">
        {{ family["code"] }}
      </Link>
    </template>
      <template #cell(shop_code)="{ item: family }">
          <Link :href="shopRoute(family)" class="secondaryLink">
              {{ family["shop_code"] }}
          </Link>
      </template>
      <template #cell(department_code)="{ item: family }">
          <Link v-if="family.department_slug"  :href="departmentRoute(family)" class="secondaryLink">
              {{ family["department_code"] }}
          </Link>
      </template>
  </Table>
</template>



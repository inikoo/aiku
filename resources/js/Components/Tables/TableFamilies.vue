<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { Family } from "@/types/family";


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
    default:
      return route(
        "grp.org.shops.show.catalogue.families.show",
        [route().params["organisation"], route().params["shop"], family.slug]);
  }
}

</script>

<template>
  <Table :resource="data" :name="tab" class="mt-5">
    <template #cell(code)="{ item: family }">
      <Link :href="familyRoute(family)" class="specialUnderline">
        {{ family["code"] }}
      </Link>
    </template>
  </Table>
</template>



<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 24 Jul 2024 00:46:50 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { Supplier } from "@/types/supplier";
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue";
import { useLocaleStore } from "@/Stores/locale";

defineProps<{
  data: object,
  tab?: string
}>();

const locale = useLocaleStore();



function supplierRoute(supplier: Supplier) {
  console.log(route().current())
    switch (route().current()) {
        case 'grp.org.procurement.org_suppliers.index':
            return route(
                'grp.org.procurement.org_suppliers.show',
                [
                  route().params['organisation'],
                  supplier.slug
                ]);

    }
}

</script>

<template>
  <Table :resource="data" :name="tab" class="mt-5">


    <template #cell(code)="{ item: supplier }">
      <Link :href="supplierRoute(supplier)" class="primaryLink">
        {{ supplier["code"] }}
      </Link>
    </template>
    <template #cell(number_supplier_products)="{ item: supplier }">
      {{ locale.number(supplier.number_supplier_products) }}
    </template>
    <template #cell(location)="{ item: supplier }">
      <AddressLocation :data="supplier['location']" />
    </template>
  </Table>
</template>



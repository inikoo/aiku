<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 18 Mar 2024 13:45:06 Malaysia Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { FulfilmentCustomer } from "@/types/Customer";
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue";
import { useFormatTime } from "@/Composables/useFormatTime";
import { useLocaleStore } from "@/Stores/locale";

const props = defineProps<{
    data: object,
    tab?: string
}>();

const locale = useLocaleStore();

// function favouriteRoute(favourite: {}) {
//     switch (route().current()) {
//         case "grp.org.shops.show.catalogue.products.current_products.show":
//             return route(
//                 "grp.org.shops.show.crm.customers.show",
//                 [
//                 route().params["organisation"],
//                 route().params["shop"],
//                 favourite.slug
//                 ]);
//         default:
//             return route(
//                 "grp.org.shops.show.crm.customers.show",
//                 [
//                     route().params["organisation"],
//                     route().params["shop"],
//                     customer.slug
//                 ]);;
//     }
// }

// function customerRoute(customer: FulfilmentCustomer) {
//     switch (route().current()) {
//         case "shops.show.customers.index":
//             return route(
//                 "grp.org.shops.show.crm.customers.show",
//                 [customer.shop_slug, customer.slug]);
//         case "grp.fulfilment.customers.index":
//             return route(
//                 "grp.fulfilment.customers.show",
//                 [customer.slug]);
//         default:
//             return route(
//                 "grp.org.shops.show.crm.customers.show",
//                 [
//                     route().params["organisation"],
//                     route().params["shop"],
//                     customer.slug
//                 ]);
//     }
// }

// function shopRoute(customer: FulfilmentCustomer) {
//     switch (route().current()) {
//         case "shops.show.customers.index":
//             return route(
//                 "shops.show",
//                 [customer.shop_slug]);
//         default:
//             return route(
//                 "shops.show",
//                 [customer.shop_slug]);
//     }
// }
</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(scope)="{ item: attachment }">
                {{ attachment["scope"] }}
        </template>
        <template #cell(caption)="{ item: attachment }">
                {{ attachment["caption"] }}
        </template>
    </Table>
</template>



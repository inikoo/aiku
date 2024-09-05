<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { DeliveryNote } from "@/types/delivery-note";
import { Tab } from "@headlessui/vue";
import type {Links, Meta} from "@/types/Table";

const props = defineProps<{
    data: {
    data: {}[]
    links: Links
    meta: Meta
  },
  tab?: string
}>();


function deliveryNoteRoute(deliveryNote: DeliveryNote) {
    switch (route().current()) {
        case "shops.show.orders.show":
            return route(
                "shops.show.orders.show.delivery-notes.show",
                [route().params["shop"], route().params["order"], deliveryNote.slug]);
        case "orders.show":
            return route(
                "orders.show,delivery-notes.show",
                [route().params["order"], deliveryNote.slug]);
        case "shops.show.delivery-notes.index":
            return route(
                "shops.show.delivery-notes.show",
                [deliveryNote.shop_id, deliveryNote.slug]);
        case "grp.org.warehouses.show.dispatching.delivery-notes":
            return route(
                "grp.org.warehouses.show.dispatching.delivery-notes.show",
                [route().params["organisation"], route().params["warehouse"], deliveryNote.slug]);
        default:
            return route(
                "grp.org.warehouses.show.dispatching.delivery-notes.show",
                [route().params["organisation"], route().params["warehouse"], deliveryNote.slug]);
    }
}

</script>

<template> 
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(reference)="{ item: deliveryNote }">
            <Link :href="deliveryNoteRoute(deliveryNote)">
                {{ deliveryNote["reference"] }}
            </Link>
        </template>
    </Table>
</template>



<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Feb 2024 19:17:38 CEST Time, Plane Madrid - Mexico City
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { WebUser } from "@/types/web-user";

const props = defineProps<{
    data: object,
    tab?: string
}>();



function webUserRoute(webUser: WebUser) {
    console.log(route().current());
    switch (route().current()) {
        case "grp.org.fulfilments.show.crm.customers.show.web-users.index":
            return route(
                "grp.org.fulfilments.show.crm.customers.show.web-users.show",
                [route().params.organisation, route().params.fulfilment, route().params.fulfilmentCustomer, webUser.slug]
            );

        case "grp.org.shops.show.web.web-users.index":
            return route(
                "grp.org.shops.show.web.web-users.show",
                [route().params.organisation, route().params.shop, webUser.slug]
            );
        case "grp.org.fulfilments.show.web.web-users.index":
            return route(
                "grp.org.fulfilments.show.web.web-users.show",
                [route().params.organisation, route().params.fulfilment, webUser.slug]);

        case  "grp.org.shops.show.web.websites.show":
            return route(
                "grp.org.shops.show.web.websites.show.web-users.show",
                [route().params.organisation, route().params.shop, route().params.website, webUser.slug]);
        case 'grp.org.fulfilments.show.crm.customers.show':
            return route(
                "grp.org.fulfilments.show.crm.customers.show.web-users.show",
                [route().params.organisation, route().params.fulfilment,  route().params.fulfilmentCustomer, webUser.slug]);
    }
}

</script>

<template>

    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(username)="{ item: webUser }">
            <Link :href="webUserRoute(webUser)" class="primaryLink">
                {{ webUser["username"] }}
            </Link>
        </template>

        <template #cell(is_root)="{ item: webUser }">
            {{ webUser["is_root"] }}
        </template>

    </Table>


</template>



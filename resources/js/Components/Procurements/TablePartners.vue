<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 18 Mar 2024 13:45:06 Malaysia Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import { FulfilmentCustomer } from "@/types/Customer"
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"

const props = defineProps<{
    data: {}
    tab?: string
}>()

const locale = useLocaleStore()


function partnerRoute(partner: {}) {
    switch (route().current()) {
        case "grp.org.procurement.org_partners.index":
            return route(
                "grp.org.procurement.org_partners.show",
                [ route().params["organisation"], partner.id])
        default:
            return route(
                "grp.org.procurement.org_partners.index",
                [ route().params["organisation"], partner.id])
    }
}
</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: partner }">
            <Link :href="partnerRoute(partner)" class="primaryLink">
            {{ partner["code"] }}
            </Link>
        </template>
        <!-- <template #cell(reference)="{ item: customer }">
            <Link :href="customerRoute(customer)" class="primaryLink">
            {{ customer["reference"] }}
            </Link>
        </template>
        <template #cell(shop)="{ item: customer }" class="primaryLink">
            <Link :href="shopRoute(customer)">
            {{ customer["shop"] }}
            </Link>
        </template>
        <template #cell(location)="{ item: customer }">
            <AddressLocation :data="customer['location']" />
        </template>
        <template #cell(created_at)="{ item: customer }">
            <div class="text-gray-500">{{ useFormatTime(customer["created_at"], {
                localeCode: locale.language.code,
                formatTime: "Ymd" }) }}</div>
        </template> -->
    </Table>
</template>
<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { FulfilmentCustomer } from "@/types/Customer"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheck, faTimes } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { useFormatTime } from "@/Composables/useFormatTime"
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue"
import { inject } from 'vue'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
import Icon from '@/Components/Icon.vue'


library.add(faCheck, faTimes)

const props = defineProps<{
    data: {}
    tab?: string
}>()

const locale = inject('locale', aikuLocaleStructure)

function customerRoute(customer: FulfilmentCustomer) {
    // switch (route().current()) {
    //     default:
    //         return route(
    //             'grp.org.fulfilments.show.crm.customers.show',
    //             [
    //                 route().params['organisation'],
    //                 route().params['fulfilment'],
    //                 customer.slug
    //             ])
    // }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(state)="{ item: storedItemAudit }">
            <Icon :data="storedItemAudit['state_icon']" class="px-1" />
        </template>

        
        <template #cell(audited_at)="{ item: customer }">
            <div class="text-gray-500 text-right">{{ useFormatTime(customer["audited_at"]) }}</div>
        </template>
        <!-- <template #cell(status)="{ item: customer }">
            <div v-tooltip="customer.status_icon.tooltip" class="px-1 py-0.5">
                <FontAwesomeIcon :icon='customer.status_icon.icon' :class='customer.status_icon.class' fixed-width
                    aria-hidden='true' />
            </div>
        </template>

        <template #cell(reference)="{ item: customer }">
            <Link :href="customerRoute(customer)" class="primaryLink">
            {{ customer['reference'] }}
            </Link>
        </template>
        <template #cell(last_invoiced_at)="{ item: customer }">
            <div class="text-gray-500 text-right">{{ useFormatTime(customer["last_invoiced_at"], {
                localeCode:
                    locale.language.code, formatTime: "aiku" }) }}</div>
        </template>
        <template #cell(invoiced_net_amount)="{ item: customer }">
            <div class="text-gray-500">{{ useLocaleStore().currencyFormat(customer.currency_code, customer.sales_all)
                }}</div>
        </template>
        <template #cell(location)="{ item: customer }">
            <AddressLocation :data="customer['location']" />
        </template>
        <template #cell(sales_all)="{ item: customer }">
            <div class="text-gray-500">{{ useLocaleStore().currencyFormat(customer.currency_code, customer.sales_all)
                }}</div>
        </template> -->
    </Table>
</template>

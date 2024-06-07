<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 02 Apr 2024 20:10:35 Central Indonesia Time, Sanur , Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { routeType } from '@/types/route'
import { PalletCustomer, PieCustomer } from '@/types/Pallet'
import { faLink } from '@far'
import { faSync, faCalendarAlt, faEnvelope, faPhone } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import Agreement from '@/Components/Rental/Agreement.vue'
import { useForm } from '@inertiajs/vue3'
import RentalTable from '@/Components/Rental/Table.vue'
import RentalBluprint from './BluprintAgreedPrice/rental'
import PhysicalGoodsBluprint from './BluprintAgreedPrice/physicalGoods'
import ServicesBluprint from './BluprintAgreedPrice/services'


library.add(faLink, faSync, faCalendarAlt, faEnvelope, faPhone)

const props = defineProps<{
    data: {
        customer: PalletCustomer
        fulfilment_customer: {
            radioTabs: {
                [key: string]: boolean
            }
            number_pallets?: number
            number_pallets_state_received?: number
            number_stored_items?: number
            number_pallets_deliveries?: number
            number_pallets_returns?: number
        }
        updateRoute: routeType
        pieData: {
            [key: string]: PieCustomer
        }
        webhook: {
            webhook_access_key: string | null
            domain: string
            route: routeType
        }
        rental_agreement: {
            stats?: {
                data: {
                    id: number
                    slug: string
                    reference: string
                    state: string
                    billing_cycle: string
                    pallets_limit: number
                    route: routeType
                }
            }
            createRoute: routeType
        }
    },
    tab: string
}>()

console.log('sss',props)

const setData = { data: props.data }

const form = useForm(setData)

const tabs = 
    [
        {
            title: 'Rentals',
            value: 'rentals',
            key: 'rentals',
            tableBluprint: RentalBluprint
        },
        {
            title: 'Services',
            value: 'services',
            key: 'services',
            tableBluprint: ServicesBluprint
        },
        {
            title: 'Physical Goods',
            value: 'physical_goods',
            key: 'physical_goods',
            tableBluprint: PhysicalGoodsBluprint
        }
    ]



</script>

<template>
    <Agreement :form="form" field-name="data" :fieldData="data" :tabs="tabs">
        <template #table="{ data: tabledata }">
             <RentalTable v-bind="tabledata.p" :bluprint="tabledata.tab.tableBluprint"/>
        </template>
    </Agreement>

</template>
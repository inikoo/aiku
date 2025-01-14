<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 23 May 2024 15:57:55 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import BoxNote from "@/Components/Pallet/BoxNote.vue"
import BoxAuditStoredItems from '@/Components/Box/BoxAuditStoredItems.vue'

import { PageHeading as PageHeadingTypes } from "@/types/PageHeading"
import { library } from "@fortawesome/fontawesome-svg-core"
import TableStoredItemsAudits from "@/Components/Tables/Grp/Org/Fulfilment/TableStoredItemsAudits.vue"

import DataTable from "primevue/datatable"
import Column from "primevue/column"
import Tag from "@/Components/Tag.vue"

import { Pallet, PalletDelivery } from '@/types/Pallet'
import { routeType } from "@/types/route"

import { faStickyNote, faPlus, faMinus } from '@fal'
import { faCheckCircle } from '@fad'
import Table from '@/Components/Table/Table.vue'
import { useFormatTime } from '@/Composables/useFormatTime'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Icon from '@/Components/Icon.vue'
import StoredItemsProperty from '@/Components/StoredItemsProperty.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { trans } from 'laravel-vue-i18n'
import { reactive, ref } from 'vue'
import { get, set } from 'lodash'
library.add(faStickyNote, faPlus, faMinus, faCheckCircle)

const props = defineProps<{
    data: {
        data: PalletDelivery
    }
    storedItemsRoute: {
        store: routeType
        index: routeType
        delete: routeType
    }
    title: string
    pageHead: PageHeadingTypes
    notes_data: any
    edit_stored_item_deltas: any
    fulfilment_customer: any
    route_list: {
        update: routeType
    }
}>()
console.log(props)

function palletRoute(pallet: Pallet) {
    switch (route().current()) {
        case "grp.overview.fulfilment.pallets.index":
            return route(
                "grp.org.fulfilments.show.operations.pallets.current.show",
                [
                    pallet.organisation_slug,
                    pallet.fulfilment_slug,
                    pallet.slug
                ])
        case "grp.org.fulfilments.show.operations.pallets.current.index":
            return route(
                "grp.org.fulfilments.show.operations.pallets.current.show",
                [
                    route().params["organisation"],
                    route().params["fulfilment"],
                    pallet.slug
                ])

        case "grp.org.fulfilments.show.operations.returned_pallets.index":
            return route(
                "grp.org.fulfilments.show.operations.returned_pallets.index",
                [
                    route().params["organisation"],
                    route().params["fulfilment"],
                    pallet.slug
                ])

        case "grp.org.fulfilments.show.crm.customers.show":
            return route(
                "grp.org.fulfilments.show.crm.customers.show.pallets.show",
                [
                    route().params["organisation"],
                    route().params["fulfilment"],
                    route().params["fulfilmentCustomer"],
                    pallet.slug
                ])
        case "grp.org.fulfilments.show.crm.customers.show.stored-item-audits.show":
            return route(
                "grp.org.fulfilments.show.crm.customers.show.pallets.show",
                [
                    route().params["organisation"],
                    route().params["fulfilment"],
                    route().params["fulfilmentCustomer"],
                    pallet.slug
                ])

        default:
            return []
    }
}


const isLoading = ref(false)
const onClickQuantity = (routex: routeType, store_item_id: number, qty: number) => {
    router.post(route(routex.name, routex.parameters), {
        stored_item_ids	: {
            [store_item_id]: {
                quantity: qty
            }
        }
    }, {
        onStart: () => {
            isLoading.value = true
        },
        onFinish: () => {
            isLoading.value = false
        }
    })
}

// Helper to give color on change qty stored items
interface StoredItemsQuantity {
    [key: string]: {  // row index of Table
        [key: string]: number  // stored item id
    }
}
const storedItemsQuantity = reactive<StoredItemsQuantity>({
})
</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <div class="grid grid-cols-2 h-fit lg:max-h-64 w-full lg:justify-center border-b border-gray-300">
        <BoxNote v-for="(note, index) in notes_data" :key="index + note.label" :noteData="note"
            :updateRoute="route.update" />
    </div>
    <BoxAuditStoredItems :auditData="data.data" :boxStats="fulfilment_customer" />
    <!-- <TableStoredItemsAudits :data="edit_stored_item_deltas" tab="edit_stored_item_deltas" :storedItemsRoute="storedItemsRoute" /> -->

    <Table :resource="edit_stored_item_deltas" name="edit_stored_item_deltas" class="mt-5">
        <!-- Column: Reference -->
        <template #cell(reference)="{ item: pallet }">
            <component :is="pallet.slug ? Link : 'div'" :href="pallet.slug ? palletRoute(pallet) : undefined"
                :class="pallet.slug ? 'primaryLink' : ''">
                {{ pallet.reference }}
            </component>
        </template>



        <!-- Column: Customer Reference -->
        <template #cell(customer_reference)="{ item: item }">
            <div>
                {{ item.customer_reference }}
                <span v-if="item.notes" class="text-gray-400 text-xs ml-1">
                    <FontAwesomeIcon icon="fal fa-sticky-note" class="text-gray-400" fixed-width aria-hidden="true" />
                    {{ item.notes }}
                </span>
            </div>
        </template>


        <!-- Column: Icon (status and state) -->
        <template #cell(state)="{ item: pallet }">
            <div class="flex gap-x-1 gap-y-1.5">
                <Icon :data="pallet.type_icon" class="px-1" />
                <Icon :data="pallet['status_icon']" />
                <Icon :data="pallet['state_icon']" />
            </div>
        </template>


        <!-- Column: Notes -->
        <template #cell(notes)="{ item: pallet }">
            <div class="text-gray-500 italic">{{ pallet.notes }}</div>
        </template>


        <!-- Column: Customer SKUS -->
        <template #cell(stored_items)="{ proxyItem, item }">
            <!-- <div class="mb-2 -mx-4 mt-10 ring-1 ring-gray-300 sm:mx-0 sm:rounded-sm">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead>
                        <tr>
                            <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold sm:pl-6">
                                SKU
                            </th>

                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold">
                                Currenty qty.
                            </th>

                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold">
                                Check status
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="(stored, idxStored) in proxyItem.stored_items" :key="stored.id">
                            <td :class="['border-t border-gray-200', 'relative py-4 pr-3 pl-4 text-sm sm:pl-6']">
                                {{ stored.reference }}
                            </td>
                            
                            <td :class="['border-t border-gray-200', 'px-3 py-3.5 text-sm text-gray-500']">
                                {{ stored.quantity }}
                            </td>

                            <td :class="['border-t border-gray-200', 'px-3 py-3.5 text-sm text-gray-500']">
                                <div class="grid grid-cols-3 justify-center items-center">
                                    <Button @click="() => stored.quantity = stored.quantity + 1"
                                        icon="fal fa-plus" :loading="isLoading" type="tertiary" size="xs" />
                                    <div :key="stored.quantity" class="text-center tabular-nums">{{ stored.quantity }}</div>
                                    <Button @click="() => stored.quantity-1"
                                        icon="fal fa-minus" :loading="isLoading" type="tertiary" size="xs" />
                                </div>
                            </td>

                        </tr>
                    </tbody>
                </table>
            </div> -->

            <DataTable v-if="proxyItem.stored_items?.length" :value="proxyItem.stored_items">
                <Column field="reference" :header="trans('SKU')">
                </Column>

                <Column field="quantity" header="Current qty">
                    <template #body="{ data }">
                        {{ data.quantity }}
                    </template>
                </Column>

                <Column field="quantity" header="" class="w-36">
                    <template #body="{ data }">
                        <div class="grid lg:grid-cols-3 gap-y-1 gap-x-1">
                            <Button @click="() => set(storedItemsQuantity, `${item.rowIndex}.${data.id}`, get(storedItemsQuantity, `${item.rowIndex}.${data.id}`, data.quantity) - 1)" icon="fal fa-minus" :loading="isLoading" type="tertiary" size="xs" class="justify-self-center" />
                                <div class="text-center tabular-nums">
                                <Transition name="spin-to-right">
                                    <span :key="get(storedItemsQuantity, `${item.rowIndex}.${data.id}`, data.quantity)" class="text-lg" :class="get(storedItemsQuantity, `${item.rowIndex}.${data.id}`, data.quantity) > data.quantity ? 'text-green-500' : get(storedItemsQuantity, `${item.rowIndex}.${data.id}`, data.quantity) === data.quantity ? 'text-gray-500' : 'text-red-500'">
                                        {{ get(storedItemsQuantity, `${item.rowIndex}.${data.id}`, data.quantity) }}
                                    </span>
                                </Transition>
                            </div>
                            <Button @click="() => set(storedItemsQuantity, `${item.rowIndex}.${data.id}`, get(storedItemsQuantity, `${item.rowIndex}.${data.id}`, data.quantity) + 1)" icon="fal fa-plus" :loading="isLoading" type="tertiary" size="xs" class="justify-self-center" />
                        </div>
                        </template>
                </Column>

                <Column field="quantity" header="Checked" class="text-right">
                    <template #body="{ data }">
                        <div @click="onClickQuantity(proxyItem.auditRoute, data.id, data.quantity)" class="mx-auto cursor-pointer w-fit py-0.5 px-3 text-gray-500 hover:text-green-500">
                            <FontAwesomeIcon icon='fad fa-check-circle'
                                class='' fixed-width aria-hidden='true' />
                        </div>
                    </template>
                </Column>

                <!-- <Column field="quantity" header="">
                    <template #body="{ data }">
                    </template>
                </Column> -->
            </DataTable>

            <div v-else class="text-gray-400">
                
            </div>
            
            <StoredItemsProperty
                :pallet="item"
                :storedItemsRoute="storedItemsRoute"
                :editable="true"
                :saveRoute="item.auditRoute"
                class="mt-2"
            >
                <template #default="{ openModal }">
                    <Button @click="openModal" type="dashed" icon="fal fa-plus" full label="Add stored item" />
                </template>
            </StoredItemsProperty>

            <!-- {{ proxyItem.auditRoute.name }}
            {{ proxyItem.auditRoute.parameters }} -->
            <!-- {{ route(proxyItem.auditRoute.name, proxyItem.auditRoute.parameters) }} -->
            <!-- aaaaaa{{ route }}dddddd -->
        </template>

        <!-- Column: edited -->
        <template #cell(audits)="{ item }">

            <!-- <StoredItemsProperty :pallet="item" :storedItemsRoute="storedItemsRoute" :editable="true"
                :saveRoute="item.auditRoute" /> -->

            <!-- <div v-if="item.audited_at" class="flex items-center justify-center">
                <font-awesome-icon :icon="['fas', 'check-circle']" class="text-lg text-green-500 mr-2"
                    v-tooltip="`Audited at: ${useFormatTime(item.audited_at)}`" />

                <Popover>
                    <template #button="{ isOpen }">
                        <Button label="Undo" type="tertiary" size="xs" icon="fal fa-history" />
                    </template>

                    <template #content="{ open, close }">
                        <div class="font-bold text-xs mb-3">are you sure to undo the Audit ?</div>
                        <div class="flex justify-end gap-1">
                            <Button label="No" type="tertiary" size="xs" @click="close()" />
                            <Link :href="route(item.resetAuditRoute.name, item.resetAuditRoute.parameters)"
                                method="delete" type="button" preserve-scroll>
                            <Button label="Yes" size="xs" />
                            </Link>
                        </div>
                    </template>
                </Popover>


            </div> -->
        </template>


    </Table>
    <!-- {{ storedItemsQuantity }} -->

</template>

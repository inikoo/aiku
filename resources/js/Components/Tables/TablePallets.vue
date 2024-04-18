<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import Icon from '@/Components/Icon.vue'
import { library } from "@fortawesome/fontawesome-svg-core"
import { faTrashAlt } from '@far'
import { faSignOutAlt, faSpellCheck, faCheck, faTimes, faCheckDouble, faCross, faFragile, faGhost, faBoxUp } from '@fal'
import Tag from "@/Components/Tag.vue"
import Popover from '@/Components/Popover.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import Multiselect from "@vueform/multiselect"
import axios from 'axios'
import { inject, ref } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import type { Meta, Links } from '@/types/Table'
import { PalletCustomer } from '@/types/Pallet'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

library.add( faTrashAlt, faSignOutAlt, faSpellCheck, faCheck, faTimes, faCheckDouble, faCross, faFragile, faGhost, faBoxUp )

const isMovePallet = inject('isMovePallet', false)

const props = defineProps<{
    data: {
        data: {}[]
        links: Links
        meta: Meta
    },
    tab?: string
}>()


function palletRoute(pallet: PalletCustomer) {
    switch (route().current()) {
        case 'grp.org.fulfilments.show.operations.pallets.index':
            return route(
                'grp.org.fulfilments.show.operations.pallets.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    pallet.slug
                ])
        case 'grp.org.warehouses.show.fulfilment.pallets.index':
            return route(
                'grp.org.warehouses.show.fulfilment.pallets.show',
                [
                    route().params['organisation'],
                    route().params['warehouse'],
                    pallet.slug
                ])

        case 'grp.org.warehouses.show.infrastructure.locations.show':
            return route(
                'grp.org.warehouses.show.infrastructure.locations.show.pallets.show',
                [
                    route().params['organisation'],
                    route().params['warehouse'],
                    route().params['location'],
                    pallet.slug
                ])
        case 'grp.org.fulfilments.show.crm.customers.show':
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallets.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['fulfilmentCustomer'],
                    pallet.slug
                ])

        default:
            return []
    }
}

function fulfilmentCustomerRoute(pallet: PalletCustomer) {
    // console.log(route().current())
    switch (route().current()) {

        case 'grp.org.fulfilments.show.operations.pallets.index':
            return route(
                'grp.org.fulfilments.show.crm.customers.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    pallet.fulfilment_customer_slug
                ])

        default:
            return []
    }
}

const palletSelected = ref<{[key: string]: number} | null>({
    abc: 1,
})  // Helper on which pallet selected to move
const isLoading = ref(false)
const locationsList = ref([])

// Method: Get locations list from current Warehouse
const getLocationsList = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(route('grp.org.warehouses.show.infrastructure.locations.index', { "organisation": "awa", "warehouse": "ac" }))

        // Add 'disabled' key to current location
        locationsList.value = response.data.data.map(loc => {
            if (loc.slug == route().params.location) {
                return {
                    ...loc,
                    disabled: true
                }
            }
            return loc
        })

        // console.log('resposne', locationsList.value)
        isLoading.value = false
    } catch (error) {
        console.error(error)
        isLoading.value = false
        // notify({
        //     title: "Failed",
        //     text: "Error while fetching data",
        //     type: "error"
        // })
    }
}

// Method: On submit move pallet
const onMovePallet = async (url: string, locationId: number, palletReference: string, closePopup: Function) => {
    try {
        axios.patch(url, {
            location_id: locationId
        })

        // Delete data in Frontend
       /*  const indexToDelete = props.data.data.findIndex(item => item.reference === palletReference);
        // Check if the element exists (index !== -1)
        if (indexToDelete !== -1) {
            props.data.meta.total = props.data.meta.total-1
            props.data.data.splice(indexToDelete, 1)
        } */

        notify({
            title: "Pallet moved!",
            text: "Pallet has been moved to " + locationsList.value.filter(loc => loc.id == locationId)[0].code,
            type: "success"
        }),
        closePopup()
    } catch (e) {
        console.error(e)
    }
}



</script>

<template>
    <!-- <pre>{{ props.data.data[0] }}</pre> -->
    <Table :resource="data" :name="tab" class="mt-5" is-check-box="true">
        <!-- Column: Reference -->
        <template #cell(reference)="{ item: pallet }">
            <component :is="pallet.slug ? Link : 'div'" :href="pallet.slug ? palletRoute(pallet) : undefined" :class="pallet.slug ? 'specialUnderline' : ''">
                {{ pallet.reference }}
            </component>
        </template>

        <!-- Column: Pallet Reference -->
        <template #cell(pallet_referencexxx)="{ item: pallet }">
            <Link :href="palletRoute(pallet)" class="specialUnderline">
                {{ pallet.reference }}
            </Link>
        </template>
        
        <!-- Column: Customer Reference -->
		<template #cell(customer_reference)="{ item: item }">
			<div>
                {{ item.customer_reference }}
                <span v-if="item.notes" class="text-gray-400 text-xs ml-1">
                    <FontAwesomeIcon icon='fal fa-sticky-note' class='text-gray-400' fixed-width aria-hidden='true' />
                    {{ item.notes }}
                </span>
            </div>
		</template>

        <template #cell(fulfilment_customer_namex)="{ item: pallet }">
            <Link :href="fulfilmentCustomerRoute(pallet)" class="specialUnderlineSecondary">
                {{ pallet['fulfilment_customer_name'] }}
            </Link>
        </template>

        <!-- Column: State -->
        <template #cell(state)="{ item: pallet }">
            <Icon :data="pallet['status_icon']" />   <Icon :data="pallet['state_icon']"  />
        </template>

        <!-- Column: Notes -->
        <template #cell(notes)="{ item: pallet }">
            <div class="text-gray-500 italic">{{ pallet.notes }}</div>
        </template>

        <!-- Column: Stored Items -->
        <template #cell(stored_items)="{ item: pallet }">
            <div v-if="pallet.stored_items.length" class="flex flex-wrap gap-x-1 gap-y-1.5">
                <Tag v-for="item of pallet.stored_items" :theme="item.id"
                    :label="`${item.reference} (${item.quantity})`" :closeButton="false" :stringToColor="true">
                    <template #label>
                        <div class="whitespace-nowrap text-xs">
                            {{ item['reference'] }} (<span class="font-light">{{ item['quantity'] }}</span>)
                        </div>
                    </template>
                </Tag>
            </div>
            <div v-else class="text-gray-400 text-xs italic">
                No items in this pallet
            </div>
        </template>

        <!-- Column: Action (move pallet) -->
        <template #cell(actions)="{ item }">
            <div class="flex gap-x-1 gap-y-1.5">
                <!-- Action: Move Pallet -->
                <Popover v-if="item.status === 'storing' && isMovePallet" width="w-full" class="relative">
                    <template #button>
                        <Button @click="() => (locationsList.length ? '' : getLocationsList(), palletSelected?.[item.reference] ? '' : palletSelected = {[item.reference]: item.location_id})" type="secondary" tooltip="Move pallet to another location" label="Move pallet" :key="item.index" :size="'xs'" />
                    </template>
                    <template #content="{ close }">
                        <div class="w-[250px]">
                            <span class="text-xs px-1 my-2">Location:</span>
                            <div>
                                <Multiselect ref="_multiselectRef"
                                    v-model="palletSelected[item.reference]"
                                    :canClear="false"
                                    :canDeselect="false"
                                    label="code"
                                    valueProp="id"
                                    placeholder="Select location.."
                                    :options="locationsList"
                                    :noResultsText="isLoading ? 'loading...' : 'No Result'"
                                >

                                </Multiselect>
                                <!-- <p v-if="error.location_id" class="mt-2 text-sm text-red-600">{{ error.location_id }}</p> -->
                            </div>
                            <div class="flex justify-end mt-2">
                                <Button @click="() => onMovePallet(route(item.updateLocationRoute.name, item.updateLocationRoute.parameters), palletSelected?.[item.reference], item.reference, close)"
                                    type="primary"
                                    tooltip="Move pallet"
                                    :loading="isLoading"
                                    label="save"
                                    :key="item.index + palletSelected?.[item.reference]"
                                    :size="'xs'"
                                    :disabled="palletSelected?.[item.reference] == item.location_id"
                                    />
                            </div>
                        </div>
                    </template>
                </Popover>

                <!-- Action: Set as storing, damaged, lost -->
                <div v-if="item.status === 'storing' && isMovePallet" class="flex gap-x-1 gap-y-2">
                    <Button label="Set as damaged" type="negative" iconRight="fal fa-fragile" size="xs" />
                    <Button label="Set as lost" type="negative" iconRight="fal fa-ghost" size="xs" />
                </div>
                <div v-else-if="(item.status === 'lost' || item.status === 'damaged') && isMovePallet">
                    <Button label="Undo" type="tertiary" icon="fal fa-box-up" size="xs" v-tooltip="`Set pallet as stored`" />
                </div>
            </div>
        </template>


        <template #cell(type_icon)="{ item: pallet }">
			<Icon :data="pallet.type_icon" class="px-1" />
		</template>


    </Table>
</template>

<style src="@vueform/multiselect/themes/default.css"></style>

<style lang="scss">
.multiselect-tags-search {
    @apply focus:outline-none focus:ring-0
}

.multiselect.is-active {
    @apply shadow-none
}

// .multiselect-tag {
//     @apply bg-gradient-to-r from-lime-300 to-lime-200 hover:bg-lime-400 ring-1 ring-lime-500 text-lime-600
// }

.multiselect-tags {
    @apply m-0.5
}

.multiselect-tag-remove-icon {
    @apply text-lime-800
}

.multiselect-dropdown {
    min-height: fit-content;
    max-height: 120px !important;
}
</style>

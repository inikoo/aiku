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
import { faSignOutAlt } from '@fal'
import Tag from "@/Components/Tag.vue"
import Popover from '@/Components/Popover.vue'
import SelectQuery from '@/Components/SelectQuery.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import Multiselect from "@vueform/multiselect"
import axios from 'axios'
import { ref } from 'vue'
import { notify } from '@kyvg/vue3-notification'

library.add(
    faTrashAlt, faSignOutAlt
)
const props = defineProps<{
    data: {},
    tab?: string
}>()


function palletRoute(pallet: Pallet) {
    switch (route().current()) {
        case 'grp.org.fulfilments.show.operations.pallets.index':
            return route(
                'grp.org.fulfilments.show.operations.pallets.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    pallet['reference']
                ])
        case 'grp.org.warehouses.show.fulfilment.pallets.index':
            return route(
                'grp.org.warehouses.show.fulfilment.pallets.show',
                [
                    route().params['organisation'],
                    route().params['warehouse'],
                    pallet['reference']
                ])

        case 'grp.org.warehouses.show.infrastructure.locations.show':
            return route(
                'grp.org.warehouses.show.infrastructure.locations.show.pallets.show',
                [
                    route().params['organisation'],
                    route().params['warehouse'],
                    route().params['location'],
                    pallet['reference']
                ])
        case 'grp.org.fulfilments.show.crm.customers.show':
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallets.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['fulfilmentCustomer'],
                    pallet['reference']
                ])

        default:
            return []
    }
}

function fulfilmentCustomerRoute(pallet: Pallet) {
    console.log(route().current())
    switch (route().current()) {

        case 'grp.org.fulfilments.show.operations.pallets.index':
            return route(
                'grp.org.fulfilments.show.crm.customers.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    pallet['fulfilment_customer_slug']
                ])

        default:
            return []
    }
}

const isLoading = ref(false)
const locationsList = ref([])
const getLocationsList = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(route('grp.org.warehouses.show.infrastructure.locations.index', { "organisation": "awa", "warehouse": "ac" })
        // , {
        //     params: {
        //         [`filter[global]`]: q.value,
        //         page: page.value,
        //         perPage: 10,
        //     }
        // }
        )

        locationsList.value = response.data.data
        console.log('resposne', response)
        // onGetOptionsSuccess(response);
        isLoading.value = false;
    } catch (error) {
        console.log(error);
        isLoading.value = false;
        notify({
            title: "Failed",
            text: "Error while fetching data",
            type: "error"
        });
    }
}

</script>

<template>
    <!-- <pre>{{ props.data }}</pre> -->
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(reference)="{ item: pallet }">
            <Link :href="palletRoute(pallet)" class="specialUnderline">
                {{ pallet['reference'] }}
            </Link>
        </template>

        <template #cell(fulfilment_customer_namex)="{ item: pallet }">
            <Link :href="fulfilmentCustomerRoute(pallet)" class="specialUnderlineSecondary">
                {{ pallet['fulfilment_customer_name'] }}
            </Link>
        </template>

        <template #cell(actions)="{ item: pallet }">
        <!-- {{pallet}} -->
            <Popover width="w-full" class="relative">
                <template #button>
                    <Button type="secondary" tooltip="Move pallet" label="Move pallet" :key="pallet.index" :size="'xs'" />
                </template>

                <template #content="{ close: closed }">
                    <div class="w-[250px]">
                        <span class="text-xs px-1 my-2">Location: </span>
                        <div>
                            <Multiselect ref="_multiselectRef"
                                v-model="pallet.location_id"
                                label="code" valueProp="id"
                                placeholder="hehehehe"
                                :options="locationsList"
                                :noResultsText="isLoading ? 'loading...' : 'No Result'"
                                @open="() => locationsList.length ? '' : getLocationsList()"
                            >
                                
                            </Multiselect>
                            <!-- <p v-if="error.location_id" class="mt-2 text-sm text-red-600">{{ error.location_id }}</p> -->
                        </div>
                        <div class="flex justify-end mt-2">
                        <!-- {{ pallet.updateLocationRoute.parameters }} -->
                            <Link :href="route(pallet.updateLocationRoute.name, pallet.updateLocationRoute.parameters)" method="patch" :data="{location_id: pallet.location_id}">
                                <Button type="primary" tooltip="Move pallet" label="save" :key="pallet.index" :size="'xs'" />
                            </Link>
                        </div>
                    </div>
                </template>
            </Popover>
        </template>

        <template #cell(state)="{ item: pallet }">
            <Icon :data="pallet['state_icon']" class="px-1" />
        </template>

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
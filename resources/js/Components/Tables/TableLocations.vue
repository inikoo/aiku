<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 14:00:48 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { Location } from "@/types/location"
import { ref } from 'vue'
import axios from 'axios'
import { notify } from '@kyvg/vue3-notification'
import Tag from '@/Components/Tag.vue'
import Multiselect from '@vueform/multiselect'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faBox, faHandHoldingBox, faPallet } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faBox, faHandHoldingBox, faPallet)

const props = defineProps<{
    data: {
        // tagsList: {}[]
        tags: tag[]
    },
    tab?: string
    tagRoute?: {}
}>()

interface tag {
    id: number
    slug: string
    name: string
    type: boolean
}

function locationRoute(location: Location) {
    switch (route().current()) {
        case 'grp.org.warehouses.show.infrastructure.dashboard':
        case 'grp.org.warehouses.show.infrastructure.locations.index':
            return route(
                'grp.org.warehouses.show.infrastructure.locations.show',
                [route().params['organisation'], route().params['warehouse'], location.slug])
        case 'grp.org.warehouse-areas.show':
        case 'grp.org.warehouse-areas.locations.index':
            return route(
                'grp.org.warehouse-areas.show.locations.show',
                [route().params['organisation'], route().params['warehouseArea'], location.slug])

        case 'grp.org.warehouses.show.infrastructure.warehouse-areas.show':
        case 'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.index':
            return route(
                'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.show',
                [route().params['organisation'], route().params['warehouse'], route().params['warehouseArea'], location.slug])
        default:
            return route(
                'grp.org.locations.show',
                [route().params['organisation'], location.slug])
    }

}

const tagsListTemp = ref<tag[]>(props.data.tags || ["ee", 'cxz'])

// Add new Tag
const addNewTag = async (option: tag) => {
    try {
        const response: any = await axios.post(route('grp.models.location.tag.store', 1),
            {name: option.name},
            {
                headers: {"Content-Type": "multipart/form-data"},
            }
        )

        tagsListTemp.value.push(response.data.data)  // (manipulation) Add new data to reactive data
        return option
    } catch (error: any) {
        notify({
            title: "Failed to add new tag",
            text: error,
            type: "error"
        })
        return false
    }
}

// On update data Tags (add tag or delete tag)
const updateTagItemTable = async (idTag: number[], idData: number) => {
    try {
        const response = await axios.patch(route('grp.models.location.tag.attach', idData),
            { tags: idTag },
        )
    } catch (error: any) {
        notify({
            title: "Failed to update tag",
            text: error,
            type: "error"
        })
        return false
    }
}

</script>

<template><pre>{{ data }}</pre>
    <Table :resource="data" :name="tab" class="mt-5">
        <!-- Column: Code -->
        <template #cell(code)="{ item: location }">
            <Link :href="locationRoute(location)" class="specialUnderline">
                {{ location['code'] }}
            </Link>
        </template>

        <!-- Column: Scope -->
        <template #cell(scope)="{ item: location }">
            <div class="flex">
                <div v-tooltip="location.allow_stocks ? 'Allow stock' : 'No stock'" class="px-1 py-0.5">
                    <FontAwesomeIcon icon='fal fa-box' fixed-width aria-hidden='true'
                        :class="[location.allow_stocks ? location.has_stock_slots ? 'text-green-500' : 'text-green-500/50' : location.has_stock_slots ? 'text-red-500' : 'text-gray-400']"
                    />
                </div>
                <div v-tooltip="location.allow_stocks ? 'Allow dropshipping' : 'No dropshipping'" class="px-1 py-0.5">
                    <FontAwesomeIcon icon='fal fa-hand-holding-box' class='' fixed-width aria-hidden='true'
                        :class="[location.allow_dropshipping ? location.has_dropshipping_slots ? 'text-green-500' : 'text-green-500/50' : location.has_dropshipping_slots ? 'text-red-500' : 'text-gray-400']"
                    />
                </div>
                <div v-tooltip="location.allow_stocks ? 'Allow fulfilment' : 'No fulfilment'" class="px-1 py-0.5">
                    <FontAwesomeIcon icon='fal fa-pallet' class='' fixed-width aria-hidden='true'
                        :class="[location.allow_fulfilment ? location.has_fulfilment ? 'text-green-500' : 'text-green-500/50' : location.has_fulfilment ? 'text-red-500' : 'text-gray-400']"
                    />
                </div>
            </div>

            <!-- Stocks: {{ location.allow_stocks }} {{ location.has_stock_slots }}
            Dropshipping: {{ location.allow_dropshipping }} : {{ location.has_dropshipping_slots }}
            Fulfilment: {{ location.allow_fulfilment }} {{ location.has_fulfilment }} -->

        </template>

        <!-- Column: Locations -->
        <template #cell(locations)="{ item }">
            <div class="min-w-[200px] relative p-0">
                <!-- <div v-if="true" class="flex gap-x-1 gap-y-1.5 mb-2">
                    <Tag v-for="tag in item.tags"
                        :label="tag"
                        :stringToColor="true"
                        size="sm"
                    />
                </div> -->

                <Multiselect v-model="item.tags"
                    :key="item.id"
                    mode="tags"
                    placeholder="Select the tag"
                    valueProp="name"
                    trackBy="name"
                    label="name"
                    @change="(idTag) => (updateTagItemTable(idTag, item.id))"
                    :close-on-select="false"
                    :searchable="true"
                    :create-option="true"
                    :on-create="addNewTag"
                    :caret="false"
                    :options="tagsListTemp"
                    noResultsText="No one left. Type to add new one."
                    appendNewTag
                >
                    <template #tag="{ option, handleTagRemove, disabled }: {option: tag, handleTagRemove: Function, disabled: boolean}">
                        <div class="px-0.5 py-[3px]">
                            <Tag
                                :label="option.name"
                                :closeButton="true"
                                :stringToColor="true"
                                size="sm"
                                @onClose="(event) => handleTagRemove(option, event)"
                            />
                        </div>
                    </template>
                </Multiselect>
            </div>
        </template>
    </Table>
</template>

<style src="@vueform/multiselect/themes/default.css"></style>

<style lang="scss">
.multiselect-tags-search {
    @apply focus:outline-none focus:ring-0 focus:border-none h-full #{!important}
}

.multiselect.is-active {
    @apply shadow-none
}

// .multiselect-tag {
//     @apply bg-gradient-to-r from-lime-300 to-lime-200 hover:bg-lime-400 ring-1 ring-lime-500 text-lime-600
// }

.multiselect-tags-search-wrapper {
    @apply mb-0 #{!important}
}

.multiselect-tags {
    @apply my-0.5 #{!important}
}

.multiselect-tags-search {
    @apply px-1 #{!important}
}

.multiselect-tag-remove-icon {
    @apply text-lime-800
}
</style>

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 May 2024 18:31:26 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { Location } from "@/types/location"
import { onMounted, onUnmounted, ref } from 'vue'
import axios from 'axios'
import { notify } from '@kyvg/vue3-notification'
import Tag from '@/Components/Tag.vue'
import Multiselect from '@vueform/multiselect'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faBox, faHandHoldingBox, faPallet, faPencil } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faBox, faHandHoldingBox, faPallet, faPencil)

const props = defineProps<{
    data: {
    },
    tagsList: tag[]
    tab?: string
    tagRoute?: {}
}>()

interface tag {
    id: number
    slug: string
    name: string
    type: string
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

const tagsListTemp = ref<tag[]>(props.tagsList)
const onEditLocation = ref(false)

// Add new Tag
const addNewTag = async (option: tag, idLocation: number) => {
    // console.log('option', option, idLocation)
    try {
        const response: any = await axios.post(route('grp.models.location.tag.store', idLocation),
            { name: option.name },
            {
                headers: { "Content-Type": "multipart/form-data" },
            }
        )
        tagsListTemp.value.push(response.data.data)  // (manipulation) Add new data to reactive data
        // return option
    } catch (error: any) {
        notify({
            title: "Failed to add new tag",
            text: error,
            type: "error"
        })
        // return false
    }
}

// On update data Tags (add tag or delete tag)
const updateTagItemTable = async (tags: string[], idLocation: number) => {
    try {
        await axios.patch(route('grp.models.location.tag.attach', idLocation),
            { tags: tags },
        )

        // Refetch the data of Table to update the item.tags (v-model doesn't work)
        router.reload(
            {
                only: ['locations']
            }
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

onMounted(() => {
    if (typeof window !== 'undefined') {
        document.addEventListener('keydown', (e) => e.keyCode == 27 ? onEditLocation.value = false : '')
    }
})

onUnmounted(() => {
    document.removeEventListener('keydown', () => false)
})

</script>

<template>
    <!-- <pre>{{ tagsListTemp }}</pre> -->
    <Table :resource="data" :name="tab" class="mt-5">
        <!-- Column: Code -->
        <template #cell(code)="{ item: location }">
            <Link :href="locationRoute(location)" class="primaryLink">
                {{ location.code }}
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
                <div v-tooltip="location.allow_dropshipping ? 'Allow dropshipping' : 'No dropshipping'" class="px-1 py-0.5">
                    <FontAwesomeIcon icon='fal fa-hand-holding-box' class='' fixed-width aria-hidden='true'
                        :class="[location.allow_dropshipping ? location.has_dropshipping_slots ? 'text-green-500' : 'text-green-500/50' : location.has_dropshipping_slots ? 'text-red-500' : 'text-gray-400']"
                    />
                </div>
                <div v-tooltip="location.allow_fulfilment ? 'Allow fulfilment' : 'No fulfilment'" class="px-1 py-0.5">
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
        <template #cell(tags)="{ item }">
            <div class="min-w-[200px] relative p-0">
                <div v-if="onEditLocation !== item.slug" class="flex gap-x-1 gap-y-1.5 mb-2">
                    <template v-if="item.tags.length">
                        <Tag v-for="tag in item.tags"
                            :label="tag"
                            :stringToColor="true"
                            size="sm"
                        />
                    </template>
                    <div v-else class="italic text-gray-400">
                        No tags
                    </div>

                    <!-- Icon: pencil -->
                    <div class="flex items-center px-1" @click="() => onEditLocation = item.slug">
                        <FontAwesomeIcon icon='fal fa-pencil' class='text-gray-400 text-lg cursor-pointer hover:text-gray-500' fixed-width aria-hidden='true' />
                    </div>
                </div>
                
                <div v-else>
                    <Multiselect v-model="item.tags"
                        :key="item.id"
                        mode="tags"
                        placeholder="Select the tag"
                        valueProp="slug"
                        trackBy="slug"
                        label="name"
                        @change="(tags) => (updateTagItemTable(tags, item.id))"
                        :close-on-select="false"
                        :searchable="true"
                        :create-option="true"
                        :on-create="(tag: tag) => addNewTag(tag, item.id)"
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

                    <div class="text-gray-400 italic text-xs">
                        Press Esc to finish edit or <span @click="() => onEditLocation = false" class="hover:text-gray-500 cursor-pointer">click here</span>.
                    </div>
                </div>
            </div>
        </template>
    </Table>
</template>

<style src="../../../../../../../node_modules/@vueform/multiselect/themes/default.css"></style>

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

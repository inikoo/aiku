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

const props = defineProps<{
    data: {},
    tab?: string,
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

const tagsListTemp = ref<tag[]>(props.tagsList || [])
// const maxId = ref(Math.max(...tagsListTemp.value.map(item => item.id)))

// Add new Tag
const addNewTag = async (option: tag) => {
    // console.log(option)
    try {
        const response: any = await axios.post(route('org.models.prospect.tag.store'),
            {name: option.name},
            {
                headers: {"Content-Type": "multipart/form-data"},
            }
        )
        // console.log(response.data)
        // maxId.value++
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
        const response = await axios.post(route('org.models.prospect.tag.attach', idData),
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

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: location }">
            <Link :href="locationRoute(location)" class="specialUnderline">
                {{ location['code'] }}
            </Link>
        </template>

        <!-- Multiselect -->
        <template #cell(locations)="{ item }">
            <div class="min-w-[200px]">
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
    @apply focus:outline-none focus:ring-0
}

.multiselect.is-active {
    @apply shadow-none
}

.multiselect-tag {
    @apply bg-gradient-to-r from-lime-300 to-lime-200 hover:bg-lime-400 ring-1 ring-lime-500 text-lime-600
}

.multiselect-tags {
    @apply m-0.5
}

.multiselect-tag-remove-icon {
    @apply text-lime-800
}
</style>
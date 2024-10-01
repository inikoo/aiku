<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { library } from '@fortawesome/fontawesome-svg-core'
import { ref } from 'vue'
import { faBrowser, faDraftingCompass, faRectangleWide, faStars, faBars } from '@fal'
import draggable from "vuedraggable"
import PanelProperties from '@/Components/Websites/Fields/PanelProperties.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Button from '@/Components/Elements/Buttons/Button.vue'
import debounce from 'lodash/debounce'
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'
import Modal from "@/Components/Utils/Modal.vue"
import BlockList from '@/Components/Fulfilment/Website/Block/BlockList.vue'

import { Root, Daum } from '@/types/webBlockTypes'
import { Root as RootWebpage } from '@/types/webpageTypes'
import { Collapse } from 'vue-collapsed'


library.add(faBrowser, faDraftingCompass, faRectangleWide, faStars, faBars)

const props = defineProps<{
    webpage: RootWebpage
    webBlockTypeCategories: Root
    isLoadingDelete: string | null
    isAddBlockLoading: string | null
}>()

const emits = defineEmits<{
    (e: 'add', value: Daum): void
    (e: 'delete', value: Daum): void
    (e: 'update', value: Daum): void
    (e: 'order', value: Object): void
    (e: 'openBlockList', value: Boolean): void
}>()

const isModalBlocksList = ref(false)
const isLoading = ref<string | boolean>(false)

const sendNewBlock = async (block: Daum) => {
    emits('add', block)
}

const sendBlockUpdate = async (block: Daum) => {
    emits('update', block)
}

const sendOrderBlock = async (block: Object) => {
    emits('order', block)
}

const sendDeleteBlock = async (block: Daum) => {
    emits('delete', block)
}


const debouncedSendUpdate = debounce((block) => sendBlockUpdate(block), 1000, { leading: false, trailing: true })
const onUpdatedBlock = (block: Daum) => {
    debouncedSendUpdate(block)
}

const onChangeOrderBlock = () => {
    let payload = {}
    props.webpage.layout.web_blocks.map((item, index) => {
        payload[item.web_block.id] = { position: index }
    })
    sendOrderBlock(payload)
}

const onPickBlock = async (block: Daum) => {
    await sendNewBlock(block)
    isModalBlocksList.value = false
}

const openModalBlockList = () => {
    isModalBlocksList.value = !isModalBlocksList.value 
    emits('openBlockList', !isModalBlocksList.value )
    
}

defineExpose({
    isModalBlocksList
})

const selectedBlockOpenPanel = ref<number | null >(null)
</script>

<template>
    <div class="flex justify-between">
        <h2 class="text-sm font-semibold leading-6">Block List</h2>
        <Button icon="fas fa-plus" type="dashed" size="xs" @click="openModalBlockList" />
    </div>
    
    <div>
        <template v-if="webpage?.layout?.web_blocks.length > 0 || isAddBlockLoading">
            <draggable
                :list="webpage.layout.web_blocks"
                handle=".handle"
                @change="onChangeOrderBlock"
                ghost-class="ghost"
                group="column"
                itemKey="column_id"
                class="mt-2 space-y-1"
            >
                <template #item="{ element, index }">
                    <div class="bg-slate-50 border border-gray-300 ">
                        <div @click="() => selectedBlockOpenPanel === index ? selectedBlockOpenPanel = null : selectedBlockOpenPanel = index"
                            class="group flex justify-between items-center gap-x-2 relative px-3 py-2 w-full cursor-pointer"
                            :class="selectedBlockOpenPanel === index ? 'bg-gray-600 text-white' : 'hover:bg-gray-100'"    
                        >
                            <div class="flex gap-x-2">
                                <div class="flex items-center justify-center">
                                    <FontAwesomeIcon icon="fal fa-bars" class="handle text-sm cursor-grab pr-3 mr-2" />
                                    <FontAwesomeIcon :icon='element?.web_block?.layout?.webpage?.icon' class='text-xs' fixed-width aria-hidden='true' />
                                </div>
                                <h3 class="text-sm font-medium select-none">
                                    {{ element.web_block.layout.name }}
                                </h3>
                            </div>
                            
                            <div v-tooltip="'Delete this block'"
                                class="p-1.5 text-base text-gray-400 hover:text-red-500 cursor-pointer"
                                @click="(e) => { e.stopPropagation(), sendDeleteBlock(element) }">
                                <LoadingIcon v-if="isLoadingDelete === ('deleteBlock' + element.id)" class="text-gray-400" />
                                <FontAwesomeIcon v-else icon='fal fa-times' fixed-width aria-hidden='true' />
                            </div>
                        </div>

                        <!-- Section: Properties panel -->
                        <Collapse as="section" :when="selectedBlockOpenPanel === index">
                            <!-- {{ index }} -->
                            <!-- <pre>{{ element.web_block.layout.data?.properties }}</pre> -->
                            <PanelProperties
                                v-model="element.web_block.layout.data.properties"
                                @update:modelValue="() => (console.log('zzz'), debouncedSendUpdate(element))"
                            />
                        </Collapse>
                    </div>
                </template>
            </draggable>

            <div v-if="isAddBlockLoading" class="mt-2 skeleton h-12 w-full rounded-md" />
        </template>

        <div v-else class="flex flex-col justify-center items-center mt-4 rounded-lg p-4 text-center h-[90%]">
            <font-awesome-icon :icon="['fal', 'browser']" class="mx-auto h-12 w-12 text-gray-400" />
            <span class="mt-2 block text-sm font-semibold text-gray-600">You don't have any blocks</span>
        </div>

        <Button icon="fas fa-plus" class="mt-3" full type="dashed" @click="openModalBlockList" />

    </div>


    <Modal :isOpen="isModalBlocksList" @onClose="openModalBlockList">
        <BlockList :onPickBlock="onPickBlock" :webBlockTypes="webBlockTypeCategories" />
    </Modal>
</template>
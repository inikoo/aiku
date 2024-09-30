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
import BlockGap from '@/Components/Websites/Fields/BlockGap.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Button from '@/Components/Elements/Buttons/Button.vue'
import debounce from 'lodash/debounce'
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'
import Modal from "@/Components/Utils/Modal.vue"
import BlockList from '@/Components/Fulfilment/Website/Block/BlockList.vue'

import { Root, Daum } from '@/types/webBlockTypes'
import { Root as RootWebpage } from '@/types/webpageTypes'


library.add(faBrowser, faDraftingCompass, faRectangleWide, faStars, faBars)

const props = defineProps<{
    webpage: RootWebpage
    webBlockTypeCategories: Root
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


</script>

<template>
    <div class="flex justify-between">
        <h2 class="text-sm font-semibold leading-6">Block List</h2>
        <Button icon="fas fa-plus" type="dashed" size="xs" @click="openModalBlockList" />
    </div>
    <div>
        <draggable v-if="webpage?.layout?.web_blocks.length > 0" :list="webpage.layout.web_blocks" handle=".handle"
            @change="onChangeOrderBlock" ghost-class="ghost" group="column" itemKey="column_id" class="mt-2 space-y-1">
            <template #item="{ element }">
                <div>
                    <Disclosure v-slot="{ open }">
                        <DisclosureButton :class="open ? 'rounded-t-lg' : 'rounded'"
                            class="group flex justify-between items-center gap-x-2 relative border border-gray-300 px-3 py-2 w-full cursor-pointer hover:bg-gray-100 bg-slate-50">
                            <div class="flex gap-x-2">
                                <div class="flex items-center justify-center">
                                    <FontAwesomeIcon icon="fal fa-bars"
                                        class="handle text-xs text-gray-700 cursor-grab pr-3 mr-2" />
                                    <FontAwesomeIcon :icon='element?.web_block?.layout?.webpage?.icon' class='text-xs'
                                        fixed-width aria-hidden='true' />
                                </div>
                                <h3 class="text-sm font-medium">
                                    {{ element.web_block.layout.name }}
                                </h3>
                            </div>

                            <div v-tooltip="'Delete this block'"
                                class="p-1.5 text-base text-gray-400 hover:text-red-500 cursor-pointer"
                                @click="(e) => { e.stopPropagation(), sendDeleteBlock(element) }">
                                <LoadingIcon v-if="isLoading === ('deleteBlock' + element.id)" class="text-gray-400" />
                                <FontAwesomeIcon v-else icon='fal fa-times' fixed-width aria-hidden='true' />
                            </div>
                        </DisclosureButton>
                        <DisclosurePanel
                            class="border border-gray-300 px-3 py-2 w-full rounded-b-lg border-t-0 mt-[-2px] text-gray-500 bg-white">
                            <BlockGap v-model="element.web_block.layout.webpage.blockLayout"
                                @update:modelValue="() => onUpdatedBlock(element)" />
                        </DisclosurePanel>
                    </Disclosure>
                </div>
            </template>
        </draggable>
        <div v-else class="flex flex-col justify-center items-center mt-4 rounded-lg p-4 text-center h-[90%]">
            <font-awesome-icon :icon="['fal', 'browser']" class="mx-auto h-12 w-12 text-gray-400" />
            <span class="mt-2 block text-sm font-semibold text-gray-600">You don't have any blocks</span>
        </div>
    </div>


    <Modal :isOpen="isModalBlocksList" @onClose="openModalBlockList">
        <BlockList :onPickBlock="onPickBlock" :webBlockTypes="webBlockTypeCategories" />
    </Modal>
</template>
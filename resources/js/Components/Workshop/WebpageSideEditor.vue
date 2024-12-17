<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { library } from '@fortawesome/fontawesome-svg-core'
import { inject, ref } from 'vue'
import { faBrowser, faDraftingCompass, faRectangleWide, faStars, faBars, faText, faEye, faEyeSlash } from '@fal'
import draggable from "vuedraggable"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Button from '@/Components/Elements/Buttons/Button.vue'
import debounce from 'lodash/debounce'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'
import Modal from "@/Components/Utils/Modal.vue"
import BlockList from '@/Components/CMS/Webpage/BlockList.vue'
import VisibleCheckmark from '@/Components/CMS/Fields/VisibleCheckmark.vue';
import SideEditor from '@/Components/Workshop/SideEditor/SideEditor.vue'
import { getBlueprint } from '@/Composables/getBlueprintWorkshop'

import { Root, Daum } from '@/types/webBlockTypes'
import { Root as RootWebpage } from '@/types/webpageTypes'
import { Collapse } from 'vue-collapsed'
import { trans } from 'laravel-vue-i18n'


library.add(faBrowser, faDraftingCompass, faRectangleWide, faStars, faBars, faText, faEye, faEyeSlash )
const modelModalBlocklist = defineModel()

const props = defineProps<{
    webpage: RootWebpage
    webBlockTypes: Root
    isLoadingblock: number | null
    isAddBlockLoading: number | null
    isLoadingDeleteBlock: number | null
}>()


const emits = defineEmits<{
    (e: 'add', value: Daum): void
    (e: 'delete', value: Daum): void
    (e: 'update', value: Daum): void
    (e: 'order', value: Object): void
    (e: 'openBlockList', value: Boolean): void
    (e: 'setVisible', value: Object): void
}>()


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


// const debouncedSendUpdate = debounce((block) => sendBlockUpdate(block), 1000, { leading: false, trailing: true })
// const onUpdatedBlock = (block) => {
//      debouncedSendUpdate(block)
// }
const onSaveWorkshop = inject('onSaveWorkshop')

const onChangeOrderBlock = (e, d) => {
    console.log('klkl', e, d)
    let payload = {}
    props.webpage.layout.web_blocks.map((item, index) => {
        payload[item.web_block.id] = { position: index }
    })
    sendOrderBlock(payload)
}

const onPickBlock = async (block: Daum) => {
    await sendNewBlock(block)
    modelModalBlocklist.value = false
}

const openModalBlockList = () => {
    modelModalBlocklist.value = !modelModalBlocklist.value
    emits('openBlockList', !modelModalBlocklist.value)
}

const setShowBlock = (e : Event, value : Object) => {
    e.stopPropagation()
    e.preventDefault()
    emits('setVisible', value)
    /* value.show = !value.show 
    onUpdatedBlock(value) */
}

defineExpose({
    modelModalBlocklist
})


const openedBlockSideEditor = inject('openedBlockSideEditor', ref(null))
</script>

<template>
    <!--  <div class="flex justify-between">
        <h2 class="text-sm font-semibold leading-6">{{trans('Blocks')}} </h2>
        <Button icon="fas fa-plus" type="dashed" size="xs" @click="openModalBlockList" />
    </div> -->
    <div class="max-h-[calc(100vh-220px)] h-full min-w-[350px] overflow-y-auto flex flex-col pr-3">
        <template v-if="webpage?.layout?.web_blocks.length > 0 || isAddBlockLoading">
            <draggable
                :list="webpage.layout.web_blocks"
                handle=".handle"
                @change="onChangeOrderBlock"
                ghost-class="ghost"
                group="column"
                itemKey="column_id"
                class="mt-2 space-y-1 shadow"
            >
                <template #item="{ element, index }">
                    <div class="bg-slate-50 border border-gray-300 ">
                        <div @click="() => openedBlockSideEditor === index ? openedBlockSideEditor = null : openedBlockSideEditor = index"
                            class="group flex justify-between items-center gap-x-2 relative w-full cursor-pointer"
                            :class="openedBlockSideEditor === index ? 'bg-indigo-500 text-white' : 'hover:bg-gray-100'">
                            <div class="h-10 flex items-center gap-x-2 py-2 px-3">
                                <div class="flex items-center justify-center">
                                    <FontAwesomeIcon icon="fal fa-bars" class="handle text-sm cursor-grab pr-3 mr-2" />
                                </div>
                                <h3 class="lg:text-sm text-xs capitalize font-medium select-none">
                                    {{ element.name || element.type }}
                                    <!-- ({{ element.id }}) -->
                                </h3>
                                <LoadingIcon v-if="isLoadingblock === element.id" class="" />
                            </div>

                            <div class="h-full text-base cursor-pointer">
                                <div class="flex h-full items-center">
                                    <div @click="(e) => setShowBlock(e, element)" class="py-1 px-2"
                                        :class="openedBlockSideEditor === index ? 'text-white' : 'text-gray-400'"    
                                    >
                                        <FontAwesomeIcon v-if="!element.show" v-tooltip="trans('Show this block')" icon="fal fa-eye-slash" class="text-base" fixed-width aria-hidden="true" />
                                        <FontAwesomeIcon v-else v-tooltip="trans('Hide this block')" icon="fal fa-eye" class="text-base" fixed-width aria-hidden="true" />
                                    </div>

                                    <div
                                        @click=" (e) => {
                                            isLoadingDeleteBlock === element.id
                                            ? false
                                            : (e.stopPropagation(), sendDeleteBlock(element))
                                        }"
                                        v-tooltip="trans('Delete this block')" class="h-10 bg-gray-200 text-red-400 hover:text-red-600 py-2.5 flex items-center justify-center px-2">
                                        <LoadingIcon v-if="isLoadingDeleteBlock === element.id" class="text-gray-400" />
                                        <FontAwesomeIcon v-else
                                            icon="fal fa-trash-alt"
                                            class=""
                                            fixed-width
                                            aria-hidden="true"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Properties panel -->
                        <Collapse v-if="element?.web_block?.layout" as="section"
                            :when="openedBlockSideEditor === index">
                            <div class="p-2">
                                <div class="px-2">
                                    <VisibleCheckmark
                                        v-model="element.visibility"
                                        @update:modelValue="onSaveWorkshop(element)"
                                    />
                                </div>
                                <SideEditor
                                    v-model="element.web_block.layout.data.fieldValue"
                                    :blueprint="getBlueprint(element.type)"
                                    @update:modelValue="(e) => (onSaveWorkshop(element))"
                                    :uploadImageRoute="{...webpage.images_upload_route, parameters : { modelHasWebBlocks: element.id }}"
                                />
                            </div>
                        </Collapse>
                    </div>
                </template>
            </draggable>

            <div v-if="isAddBlockLoading" class="mt-2 skeleton min-h-10 w-full rounded" />
        </template>

        <div v-else class="flex flex-col justify-center items-center mt-4 rounded-lg p-4 text-center h-[90%]">
            <font-awesome-icon :icon="['fal', 'browser']" class="mx-auto h-12 w-12 text-gray-400" />
            <span class="mt-2 block text-sm font-semibold text-gray-600">You don't have any blocks</span>
        </div>
    </div>

    <div class="full pr-3">
        <Button class="mt-3" full type="dashed" @click="openModalBlockList">
            <div class="text-gray-500">
                <FontAwesomeIcon icon='fal fa-plus' class='' fixed-width aria-hidden='true' />
                {{ trans('Add block') }}
            </div>
        </Button>
    </div>


    <Modal :isOpen="modelModalBlocklist" @onClose="openModalBlockList">
        <BlockList :onPickBlock="onPickBlock" :webBlockTypes="webBlockTypes" scope="webpage" />
    </Modal>
</template>


<style lang="scss" scoped>
// .container {
//     max-height: 78vh;
// }

</style>
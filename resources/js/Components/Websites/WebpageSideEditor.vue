<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { library } from '@fortawesome/fontawesome-svg-core'
import { ref, watch } from 'vue'
import { faBrowser, faDraftingCompass, faRectangleWide, faStars, faBars, faText, faEye, faEyeSlash } from '@fal'
import draggable from "vuedraggable"
import PanelProperties from '@/Components/Websites/Fields/PanelProperties.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Button from '@/Components/Elements/Buttons/Button.vue'
import debounce from 'lodash/debounce'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'
import Modal from "@/Components/Utils/Modal.vue"
import BlockList from '@/Components/Fulfilment/Website/Block/BlockList.vue'
import VisibleCheckmark from '@/Components/Websites/Fields/VisibleCheckmark.vue';
import SideEditor from '@/Components/Websites/SideEditor.vue'

import { Root, Daum } from '@/types/webBlockTypes'
import { Root as RootWebpage } from '@/types/webpageTypes'
import { Collapse } from 'vue-collapsed'
import { trans } from 'laravel-vue-i18n'
import { set } from 'lodash'


library.add(faBrowser, faDraftingCompass, faRectangleWide, faStars, faBars, faText, faEye, faEyeSlash )
const modelModalBlocklist = defineModel()

const props = defineProps<{
    webpage: RootWebpage
    webBlockTypes: Root
    isLoadingblock: string | null
    isAddBlockLoading: string | null
}>()

const emits = defineEmits<{
    (e: 'add', value: Daum): void
    (e: 'delete', value: Daum): void
    (e: 'update', value: Daum): void
    (e: 'order', value: Object): void
    (e: 'openBlockList', value: Boolean): void
}>()


const sendNewBlock = async (block: Daum) => {
    emits('add', block)
}

const sendBlockUpdate = async (block: Daum) => {
    console.log(block)
    emits('update', block)
}

const sendOrderBlock = async (block: Object) => {
    emits('order', block)
}

const sendDeleteBlock = async (block: Daum) => {
    emits('delete', block)
}


const debouncedSendUpdate = debounce((block) => sendBlockUpdate(block), 1000, { leading: false, trailing: true })
const onUpdatedBlock = (block) => {
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
    modelModalBlocklist.value = false
}

const openModalBlockList = () => {
    modelModalBlocklist.value = !modelModalBlocklist.value
    emits('openBlockList', !modelModalBlocklist.value)
}

const setShowBlock = (e,value) => {
    e.stopPropagation()
    value.show = !value.show 
    onUpdatedBlock(value)
}


defineExpose({
    modelModalBlocklist
})


const selectedBlockOpenPanel = ref<number | null>(null)
</script>

<template>
    <div class="flex justify-between">
        <h2 class="text-sm font-semibold leading-6">{{trans('Blocks')}} </h2>
        <Button icon="fas fa-plus" type="dashed" size="xs" @click="openModalBlockList" />
    </div>
    <div>
        <template v-if="webpage?.layout?.web_blocks.length > 0 || isAddBlockLoading">
            <draggable :list="webpage.layout.web_blocks" handle=".handle" @change="onChangeOrderBlock"
                ghost-class="ghost" group="column" itemKey="column_id" class="mt-2 space-y-1">
                <template #item="{ element, index }">
                    <div class="bg-slate-50 border border-gray-300 ">
                        <div @click="() => selectedBlockOpenPanel === index ? selectedBlockOpenPanel = null : selectedBlockOpenPanel = index"
                            class="group flex justify-between items-center gap-x-2 relative px-3 py-2 w-full cursor-pointer"
                            :class="selectedBlockOpenPanel === index ? 'bg-indigo-500 text-white' : 'hover:bg-gray-100'">
                            <div class="flex gap-x-2">
                                <div class="flex items-center justify-center">
                                    <FontAwesomeIcon icon="fal fa-bars" class="handle text-sm cursor-grab pr-3 mr-2" />
                                </div>
                                <h3 class="lg:text-sm text-xs capitalize font-medium select-none">
                                    {{ element.name ||  element.type }}
                                </h3>
                            </div>

                            <div
								class="p-1.5 text-base text-gray-400 hover:text-red-500 cursor-pointer">
								<div>
									<LoadingIcon
										v-if="isLoadingblock === 'deleteBlock' + element.id"
										class="text-gray-400" />

									<div v-else class="flex gap-4">
                                        <div>
                                            <FontAwesomeIcon
                                                v-tooltip="'show this block'"
                                                v-if="!element.show"
                                                icon="fal fa-eye-slash"
                                                class="text-base sm:text-lg md:text-xl lg:text-2xl"
                                                fixed-width
                                                aria-hidden="true"
                                                @click="(e) => setShowBlock(e, element)" />
                                            <FontAwesomeIcon
                                                v-tooltip="'hide this block'"
                                                v-else
                                                icon="fal fa-eye"
                                                class="text-base sm:text-lg md:text-xl lg:text-2xl"
                                                fixed-width
                                                aria-hidden="true"
                                                @click="(e) => setShowBlock(e, element)" />
                                        </div>

										<FontAwesomeIcon
											v-if="!element.show"
											icon="fal fa-times"
											v-tooltip="'Delete this block'"
											class="text-base sm:text-lg md:text-xl lg:text-2xl"
											fixed-width
											aria-hidden="true"
											@click="
												(e) => {
													e.stopPropagation(), 
                                                    sendDeleteBlock(element)
												}
											" />
									</div>
								</div>
							</div>
                        </div>

                        <!-- Section: Properties panel -->
                        <Collapse v-if="element?.web_block?.layout" as="section"
                            :when="selectedBlockOpenPanel === index">
                            <div class="p-2">
                                <div class="px-2">
                                    <VisibleCheckmark v-model="element.visibility" @update:modelValue="onUpdatedBlock(element)"/>
                                </div>
                              
                                <SideEditor 
                                    v-model="element.web_block.layout.data.fieldValue"
                                    :bluprint="element?.web_block?.layout?.blueprint"
                                    @update:modelValue="onUpdatedBlock(element)" 
                                    :uploadImageRoute="{...webpage.images_upload_route, parameters : { modelHasWebBlocks: element.id }}"
                                />
                            </div>
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

        <Button label="add new block" class="mt-3" full type="dashed" @click="openModalBlockList">
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
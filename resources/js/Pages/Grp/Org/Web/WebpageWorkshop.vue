<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import { library } from '@fortawesome/fontawesome-svg-core'
import { ref } from 'vue'
import { faBrowser, faDraftingCompass, faRectangleWide, faStars, faBars } from '@fal'
import draggable from "vuedraggable"
import BlockGap from '@/Components/Websites/Fields/BlockGap.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Button from '@/Components/Elements/Buttons/Button.vue'
import Modal from "@/Components/Utils/Modal.vue"
import BlockList from '@/Components/Fulfilment/Website/Block/BlockList.vue'
import { getComponent } from '@/Components/Fulfilment/Website/BlocksList'
import axios from 'axios'
import debounce from 'lodash/debounce'
import Publish from '@/Components/Publish.vue'
import { notify } from "@kyvg/vue3-notification"
import EmptyState from "@/Components/Utils/EmptyState.vue"
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'


library.add(faBrowser, faDraftingCompass, faRectangleWide, faStars, faBars)

const props = defineProps<{
    title: string,
    pageHead: PageHeadingTypes,
    webpage: Object
    webBlockTypes: {
        data: Array
    }
}>()

const isModalBlocksList = ref(false)
const comment = ref("")
const isLoading = ref<string | boolean>(false)
const isAddBlockLoading = ref<string | boolean>(false)
const selectedBlock = ref(null)
const data = ref({
    ...props.webpage
})


const sendNewBlock = async (block) => {
    try {
        const response = await axios.post(
            route(props.webpage.add_web_block_route.name, props.webpage.add_web_block_route.parameters),
            { web_block_type_id: block.id }
        )
        const set = { ...response.data.data }
        data.value = set
    } catch (error: any) {
        console.error('error', error)
    }
    isAddBlockLoading.value = false

}

const sendBlockUpdate = async (block) => {
    try {
        const response = await axios.patch(
            route(props.webpage.update_model_has_web_blocks_route.name, { modelHasWebBlocks: block.id }),
            { layout: block.web_block.layout }
        )
        const set = { ...response.data.data }
        data.value = set
    } catch (error: any) {
        console.error('error', error)
    }
}

const sendOrderBlock = async (block) => {
    try {
        const response = await axios.post(
            route(props.webpage.reorder_web_blocks_route.name, props.webpage.reorder_web_blocks_route.parameters),
            { positions: block }
        )
        const set = { ...response.data.data }
        data.value = set
    } catch (error: any) {
        console.error('error', error)
    }
}

const sendDeleteBlock = async (block) => {
    // console.log('block', block)
    isLoading.value = 'deleteBlock' + block.id
    try {
        const response = await axios.delete(
            route(props.webpage.delete_model_has_web_blocks_route.name, { modelHasWebBlocks: block.id })
        )
        const set = { ...response.data.data }
        data.value = set
    } catch (error: any) {
        console.error('error', error)
    }
    isLoading.value = false
}


const debouncedSendUpdate = debounce(
    (block) => sendBlockUpdate(block),
    1000,
    { leading: false, trailing: true }
)

const onUpdatedBlock = (block) => {
    debouncedSendUpdate(block)
}


const onPickBlock = async (block) => {
    isAddBlockLoading.value = true
    await sendNewBlock(block)
    isModalBlocksList.value = false

}

const onChangeOrderBlock = (moved) => {
    let payload = {}
    data.value.layout.web_blocks.map((item, index) => {
        payload[item.web_block.id] = { position: index }
    })
    sendOrderBlock(payload)
}

const setData = () => {
    console.log(data.value)
}


const onPublish = async (action : {},popover : {}) => {
    try {
        // Ensure action is defined and has necessary properties
        if (!action || !action.method || !action.name || !action.parameters) {
            throw new Error('Invalid action parameters')
        }

        isLoading.value = true

        // Make sure route and axios are defined and used correctly
        const response = await axios[action.method](route(action.name, action.parameters), {
            comment: comment.value,
            publishLayout: { blocks: data.value.layout }
        })

        popover.close()

    } catch (error) {
        // Ensure the error is logged properly
        console.error('Error:', error)

        // Ensure the error notification is user-friendly
        const errorMessage = error.response?.data?.message || error.message || 'Unknown error occurred'
        notify({
            title: 'Something went wrong.',
            text: errorMessage,
            type: 'error',
        })
    } finally {
        // Ensure loading state is updated
        isLoading.value = false
    }
};



</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-publish="{ action }">
            <!--  <Action v-if="action" :action="action" :dataToSubmit="data" /> -->
            <Publish :isLoading="isLoading" :is_dirty="data.is_dirty" v-model="comment"
                @onPublish="(popover)=>onPublish(action.route,popover)" />
        </template>
    </PageHeading>

    <div class="mx-auto px-4 py-4 sm:px-6 lg:px-8 w-full h-[85vh]">
        <div class="mx-auto grid grid-cols-4 gap-1 lg:mx-0 lg:max-w-none h-full">
            <div class="h-full overflow-auto border-2 border-dashed p-2" :class="data.layout.web_blocks?.length > 0 ? 'col-span-3' : 'col-span-4'">
                <div v-if="data.layout.web_blocks?.length">
                    <TransitionGroup tag="div" name="zzz" class="relative">
                        <section v-for="(activityItem, activityItemIdx) in data.layout.web_blocks" 
                            :style="{
                                paddingTop : `${activityItem?.web_block?.layout?.data?.blockLayout?.paddingTop?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.paddingTop?.unit}`, 
                                paddingBottom : `${activityItem?.web_block?.layout?.data?.blockLayout?.paddingBottom?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.paddingBottom?.unit}`, 
                                paddingRight : `${activityItem?.web_block?.layout?.data?.blockLayout?.paddingRight?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.paddingRight?.unit}` ,
                                paddingLeft : `${activityItem?.web_block?.layout?.data?.blockLayout?.paddingLeft?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.paddingLeft?.unit}`,
                                marginTop : `${activityItem?.web_block?.layout?.data?.blockLayout?.marginTop?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.marginTop?.unit}`,
                                marginBottom : `${activityItem?.web_block?.layout?.data?.blockLayout?.marginBottom?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.marginBottom?.unit}`,
                                marginRight : `${activityItem?.web_block?.layout?.data?.blockLayout?.marginRight?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.marginRight?.unit}`,
                                marginLeft : `${activityItem?.web_block?.layout?.data?.blockLayout?.marginLeft?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.marginLeft?.unit}`,
                            }"
                            :key="activityItem.id" @click="() => selectedBlock = activityItem" class="w-full">
                            <component :is="getComponent(activityItem?.web_block?.layout?.data?.component)"
                                :key="activityItemIdx" :webpageData="webpage" v-bind="activityItem"
                                v-model="activityItem.web_block.layout.data.fieldValue" :isEditable="true"
                                @autoSave="() => onUpdatedBlock(activityItem)"/>
                        </section>
                    </TransitionGroup>

                    <div v-if="isAddBlockLoading" class="w-full h-32 skeleton">
                    </div>
                </div>

                <div v-else>
                    <EmptyState :data="{ title: 'Pick Frist Block For Your Website', description: 'Pick block from list' }">
                        <template #button-empty-state>
                        <div class="mt-4 block">
                            <Button @click="() => isModalBlocksList = true" label="Select block" type="tertiary" icon="fal fa-plus" />
                        </div>
                        </template>
                    </EmptyState>
                </div>
      </div>

      <div v-if="data.layout.web_blocks?.length > 0" class="col-span-1 h-screen">
        <div class="border-2 bg-gray-200 p-3 h-full">
          <div class="flex justify-between">
            <h2 class="text-sm font-semibold leading-6">Block List</h2>
            <Button icon="fas fa-plus" size="xs" @click="() => (isModalBlocksList = true)" />
          </div>
          <div class="px-3">
          <draggable v-if="data?.layout?.web_blocks.length > 0" :list="data.layout.web_blocks" handle=".handle"
            @change="onChangeOrderBlock" ghost-class="ghost" group="column" itemKey="column_id" class="mt-2 space-y-1">
            <template #item="{ element, index }">
              <div>
                <Disclosure v-slot="{ open }">
                  <DisclosureButton
                    :class="open ? 'rounded-t-lg' : 'rounded'"
                    class="group flex justify-between items-center gap-x-2 relative border border-gray-300 px-3 py-2 w-full  cursor-pointer hover:bg-gray-100 bg-slate-50">
                    <div class="flex gap-x-2">
                      <div class="flex items-center justify-center">
                        <!-- <pre>{{element }}</pre> -->
                        <FontAwesomeIcon icon="fal fa-bars"
                                class="handle text-xs  text-gray-700 cursor-grab pr-3 mr-2" />
                        <FontAwesomeIcon :icon='element?.web_block?.layout?.data?.icon' class='text-xs' fixed-width
                          aria-hidden='true' />
                      </div>
                      <h3 class="text-sm font-medium">
                        {{ element.web_block.layout.name }}
                      </h3>
                    </div>

                    <div v-tooltip="'Delete this block'" class="p-1.5 text-base text-gray-400 hover:text-red-500 cursor-pointer"
                        @click="(e) => {e.stopPropagation(), sendDeleteBlock(element)}">
                        <LoadingIcon v-if="isLoading === ('deleteBlock' + element.id)" class="text-gray-400" />
                        <FontAwesomeIcon v-else icon='fal fa-times' class='' fixed-width aria-hidden='true' />
                    </div>

                  </DisclosureButton>
                  <DisclosurePanel
                    class="border border-gray-300 px-3 py-2 w-full rounded-b-lg border-t-0 mt-[-2px] text-gray-500 bg-white">
                <BlockGap v-model="element.web_block.layout.data.blockLayout"  @update:modelValue="() => onUpdatedBlock(element)" />
                  </DisclosurePanel>
                </Disclosure>
              </div>
            </template>
          </draggable>

          <!-- Section: if no blocks selected -->
          <div v-else class="flex flex-col justify-center items-center mt-4 rounded-lg p-4 text-center h-[90%]">
                            <font-awesome-icon :icon="['fal', 'browser']" class="mx-auto h-12 w-12 text-gray-400" />
                            <span class="mt-2 block text-sm font-semibold text-gray-600">You dont have block</span>
                        </div>
          </div>

          <!--  <Button
                        type="dashed"
                        icon="fal fa-plus"
                        label="Add block"
                        full
                        size="s"
                        class="mt-2"
                        @click="() => (isModalBlocksList = true)"
                    /> -->
        </div>
      </div>
    </div>
  </div>
  <Modal :isOpen="isModalBlocksList" @onClose="isModalBlocksList = false">
    <BlockList :onPickBlock="onPickBlock" :webBlockTypes="webBlockTypes" />
  </Modal>
  <div @click="setData">see data</div>
</template>

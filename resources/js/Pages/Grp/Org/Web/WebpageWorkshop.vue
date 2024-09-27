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
import { ref, inject } from 'vue'
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
import ScreenView from "@/Components/ScreenView.vue"



import { Root, Daum } from '@/types/webBlockTypes'
import { Root as RootWebpage } from '@/types/webpageTypes'
import { PageHeading as PageHeadingTypes } from '@/types/PageHeading'


library.add(faBrowser, faDraftingCompass, faRectangleWide, faStars, faBars)

const props = defineProps<{
    title: string,
    pageHead: PageHeadingTypes,
    webpage: RootWebpage
    webBlockTypes: Root
}>()

const isModalBlocksList = ref(false)
const comment = ref("")
const isLoading = ref<string | boolean>(false)
const isAddBlockLoading = ref<string | boolean>(false)
const iframeSrc = ref(route('grp.websites.preview', [route().params['website'], route().params['webpage']]))
const data = ref({ ...props.webpage })
const iframeClass = ref('w-full h-full')
const isIframeLoading = ref(true)

const reloadIframe = () => {
    iframeSrc.value = `${route('grp.websites.preview', [route().params['website'], route().params['webpage']])}?reload=${new Date().getTime()}`;
    isIframeLoading.value = true
}

const sendNewBlock = async (block: Daum) => {
    try {
        const response = await axios.post(
            route(props.webpage.add_web_block_route.name, props.webpage.add_web_block_route.parameters),
            { web_block_type_id: block.id }
        )
        const set = { ...response.data.data }
        data.value = set
        reloadIframe()
    } catch (error: any) {
        console.error('error', error)
    }
    isAddBlockLoading.value = false

}

const sendBlockUpdate = async (block: Daum) => {
    try {
        const response = await axios.patch(
            route(props.webpage.update_model_has_web_blocks_route.name, { modelHasWebBlocks: block.id }),
            { layout: block.web_block.layout }
        )
        const set = { ...response.data.data }
        data.value = set
        reloadIframe()
    } catch (error: any) {
        console.error('error', error)
    }
}

const sendOrderBlock = async (block : Object) => {
    try {
        const response = await axios.post(
            route(props.webpage.reorder_web_blocks_route.name, props.webpage.reorder_web_blocks_route.parameters),
            { positions: block }
        )
        const set = { ...response.data.data }
        data.value = set
        reloadIframe()
    } catch (error: any) {
        console.error('error', error)
    }
}

const sendDeleteBlock = async (block: Daum) => {
    // console.log('block', block)
    isLoading.value = 'deleteBlock' + block.id
    try {
        const response = await axios.delete(
            route(props.webpage.delete_model_has_web_blocks_route.name, { modelHasWebBlocks: block.id })
        )
        const set = { ...response.data.data }
        data.value = set
        reloadIframe()
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

const onUpdatedBlock = (block: Daum) => {
    debouncedSendUpdate(block)
}


const onPickBlock = async (block: Daum) => {
    isAddBlockLoading.value = true
    await sendNewBlock(block)
    isModalBlocksList.value = false

}

const onChangeOrderBlock = () => {
    let payload = {}
    data.value.layout.web_blocks.map((item, index) => {
        payload[item.web_block.id] = { position: index }
    })
    sendOrderBlock(payload)
}


const onPublish = async (action: {}, popover: {}) => {
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
        reloadIframe()
        popover.close()

    } catch (error) {
        // Ensure the error is logged properly
        console.error('Error:', error)
        const errorMessage = error.response?.data?.message || error.message || 'Unknown error occurred'
        notify({
            title: 'Something went wrong.',
            text: errorMessage,
            type: 'error',
        })
    } finally {
        isLoading.value = false
    }
};

const setIframeView = (view: String) => {
    if (view === 'mobile') {
        iframeClass.value = 'w-[375px] h-[667px] mx-auto'; // iPhone 6/7/8 size
    } else if (view === 'tablet') {
        iframeClass.value = 'w-[768px] h-[1024px] mx-auto'; // iPad size
    } else {
        iframeClass.value = 'w-full h-full'; // Full width for desktop
    }
}

const handleIframeError = () => {
    console.error('Failed to load iframe content.');
}


</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-publish="{ action }">
            <Publish :isLoading="isLoading" :is_dirty="data.is_dirty" v-model="comment"
                @onPublish="(popover) => onPublish(action.route, popover)" />
        </template>
    </PageHeading>

    <div class="grid grid-cols-5 h-[85vh]">
        <div class="col-span-1 h-full border-2 bg-gray-200 p-3">
            <div class="flex justify-between">
                <h2 class="text-sm font-semibold leading-6">Block List</h2>
                <Button icon="fas fa-plus" size="xs" @click="() => (isModalBlocksList = true)" />
            </div>
            <div class="px-3">
                <draggable v-if="data?.layout?.web_blocks.length > 0" :list="data.layout.web_blocks" handle=".handle"
                    @change="onChangeOrderBlock" ghost-class="ghost" group="column" itemKey="column_id"
                    class="mt-2 space-y-1">
                    <template #item="{ element }">
                        <div>
                            <Disclosure v-slot="{ open }">
                                <DisclosureButton :class="open ? 'rounded-t-lg' : 'rounded'"
                                    class="group flex justify-between items-center gap-x-2 relative border border-gray-300 px-3 py-2 w-full cursor-pointer hover:bg-gray-100 bg-slate-50">
                                    <div class="flex gap-x-2">
                                        <div class="flex items-center justify-center">
                                            <FontAwesomeIcon icon="fal fa-bars"
                                                class="handle text-xs text-gray-700 cursor-grab pr-3 mr-2" />
                                            <FontAwesomeIcon :icon='element?.web_block?.layout?.data?.icon'
                                                class='text-xs' fixed-width aria-hidden='true' />
                                        </div>
                                        <h3 class="text-sm font-medium">
                                            {{ element.web_block.layout.name }}
                                        </h3>
                                    </div>

                                    <div v-tooltip="'Delete this block'"
                                        class="p-1.5 text-base text-gray-400 hover:text-red-500 cursor-pointer"
                                        @click="(e) => { e.stopPropagation(), sendDeleteBlock(element) }">
                                        <LoadingIcon v-if="isLoading === ('deleteBlock' + element.id)"
                                            class="text-gray-400" />
                                        <FontAwesomeIcon v-else icon='fal fa-times' fixed-width aria-hidden='true' />
                                    </div>
                                </DisclosureButton>
                                <DisclosurePanel
                                    class="border border-gray-300 px-3 py-2 w-full rounded-b-lg border-t-0 mt-[-2px] text-gray-500 bg-white">
                                    <BlockGap v-model="element.web_block.layout.data.blockLayout"
                                        @update:modelValue="() => onUpdatedBlock(element)" />
                                </DisclosurePanel>
                            </Disclosure>
                        </div>
                    </template>
                </draggable>
                <div v-else class="flex flex-col justify-center items-center mt-4 rounded-lg p-4 text-center h-[90%]">
                    <font-awesome-icon :icon="['fal', 'browser']" class="mx-auto h-12 w-12 text-gray-400" />
                    <span class="mt-2 block text-sm font-semibold text-gray-600">You don't have any
                        blocks</span>
                </div>
            </div>
        </div>

        <div class="col-span-4 h-full flex flex-col bg-gray-200">
            <ScreenView @screenView="setIframeView" />
            <div class="border-2 h-full w-full">
                <div v-if="isIframeLoading" class="flex justify-center items-center w-full h-64 p-12 bg-white">
                    <FontAwesomeIcon icon="fad fa-spinner-third" class="animate-spin w-6" aria-hidden="true" />
                </div>

                <div class="h-full w-full bg-white">
                    <iframe :src="iframeSrc" :title="props.title" :class="[iframeClass, isIframeLoading ? 'hidden' : '' ]" 
                        @error="handleIframeError" @load="isIframeLoading = false"/>
                </div>
            </div>
        </div>

        
    </div>


    <Modal :isOpen="isModalBlocksList" @onClose="isModalBlocksList = false">
        <BlockList :onPickBlock="onPickBlock" :webBlockTypes="webBlockTypes" />
    </Modal>
</template>

<style scoped>
iframe {
    height: 100%;
    transition: width 0.3s ease;
    /* Smooth transition when changing width */
}
</style>
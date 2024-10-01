<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref, defineExpose } from 'vue'
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import axios from 'axios'
import Publish from '@/Components/Publish.vue'
import { notify } from "@kyvg/vue3-notification"
import ScreenView from "@/Components/ScreenView.vue"
import WebpageSideEditor from "@/Components/Websites/WebpageSideEditor.vue"
import Drawer from 'primevue/drawer';

import { Root, Daum } from '@/types/webBlockTypes'
import { Root as RootWebpage } from '@/types/webpageTypes'
import { PageHeading as PageHeadingTypes } from '@/types/PageHeading'

import { faBrowser, faDraftingCompass, faRectangleWide, faStars, faBars, faExternalLink } from '@fal'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'


library.add(faBrowser, faDraftingCompass, faRectangleWide, faStars, faBars)

const props = defineProps<{
    title: string,
    pageHead: PageHeadingTypes,
    webpage: RootWebpage
    webBlockTypeCategories: Root
}>()

const comment = ref("")
const isLoading = ref<string | boolean>(false)
const openDrawer = ref<string | boolean>(false)
const isAddBlockLoading = ref<string | boolean>(false)
const iframeSrc = ref(route('grp.websites.preview', [route().params['website'], route().params['webpage']]))
const data = ref({ ...props.webpage })
const iframeClass = ref('w-full h-full')
const isIframeLoading = ref(true)
const _WebpageSideEditor = ref(null)

const sendNewBlock = async (block: Daum) => {
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

const sendBlockUpdate = async (block: Daum) => {
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

const sendOrderBlock = async (block: Object) => {
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

const sendDeleteBlock = async (block: Daum) => {
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

const openFullScreenPreview = () => {
    window.open(iframeSrc.value, '_blank')
}

</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-publish="{ action }">
            <Publish :isLoading="isLoading" :is_dirty="data.is_dirty" v-model="comment"
                @onPublish="(popover) => onPublish(action.route, popover)" ref="_WebpageSideEditor"/>
        </template>
    </PageHeading>


    <!--   side editor -->
    <div class="grid grid-cols-5 h-[85vh]">
        <div class="col-span-1 md:block hidden h-full border-2 bg-gray-200 px-3 py-1 ">
            <WebpageSideEditor :webpage="data" :webBlockTypeCategories="webBlockTypeCategories"
                @update="sendBlockUpdate" @delete="sendDeleteBlock" @add="sendNewBlock" @order="sendOrderBlock"/>
        </div>
        <!--   main editor -->
        <div class="md:col-span-4 col-span-5  h-full flex flex-col bg-gray-200">
            <div class="flex justify-between">
                <div class="py-1 px-2 cursor-pointer md:hidden block" title="Desktop view" v-tooltip="'Navigation'">
                    <FontAwesomeIcon :icon='faBars' aria-hidden='true' @click="()=>openDrawer = true" />
                    <Drawer v-model:visible="openDrawer" :header="''" :dismissable="true">
                        <WebpageSideEditor ref="_WebpageSideEditor" :webpage="data" :webBlockTypeCategories="webBlockTypeCategories"
                            @update="sendBlockUpdate" @delete="sendDeleteBlock" @add="sendNewBlock"
                            @order="sendOrderBlock"  @openBlockList="()=>{openDrawer = false, _WebpageSideEditor.isModalBlocksList = true}" />
                    </Drawer>
                </div>
                <div class="flex">
                    <ScreenView @screenView="setIframeView" />
                    <div class="py-1 px-2 cursor-pointer" title="Desktop view" v-tooltip="'Preview'"
                        @click="openFullScreenPreview">
                        <FontAwesomeIcon :icon='faExternalLink' aria-hidden='true' />
                    </div>

                </div>
            </div>

            <div class="border-2 h-full w-full">
                <div v-if="isIframeLoading" class="flex justify-center items-center w-full h-64 p-12 bg-white">
                    <FontAwesomeIcon icon="fad fa-spinner-third" class="animate-spin w-6" aria-hidden="true" />
                </div>

                <div class="h-full w-full bg-white">
                    <iframe :src="iframeSrc" :title="props.title"
                        :class="[iframeClass, isIframeLoading ? 'hidden' : '']" @error="handleIframeError"
                        @load="isIframeLoading = false" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
iframe {
    height: 100%;
    transition: width 0.3s ease;
    /* Smooth transition when changing width */
}
</style>
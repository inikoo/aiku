<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"

import Button from '@/Components/Elements/Buttons/Button.vue';
import Modal from '@/Components/Utils/Modal.vue'
import EmptyState from '@/Components/Utils/EmptyState.vue';
import SideEditor from '@/Components/Websites/SideEditor.vue';
import { v4 as uuidv4 } from 'uuid';
import { notify } from "@kyvg/vue3-notification"
import axios from 'axios'
import { debounce } from 'lodash'
import Publish from '@/Components/Publish.vue'
import BlockList from '@/Components/Fulfilment/Website/Block/BlockList.vue'
import ScreenView from "@/Components/ScreenView.vue"


import { routeType } from "@/types/route"
import { PageHeading as TSPageHeading } from '@/types/PageHeading'


import { faPresentation, faCube, faText, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faHeart, faExternalLink } from '@far';
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle, faImage } from '@fas';
library.add(faPresentation, faCube, faText, faImage, faPaperclip, faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle)

const props = defineProps<{
    pageHead: TSPageHeading
    title: string
    data: {
        footer: Object
    }
    autosaveRoute: routeType
    webBlockTypeCategories: Object
}>()

const previewMode = ref(false)
const isModalOpen = ref(false)
const usedTemplates = ref(props.data.footer)
const keyTemplates = ref(uuidv4())
const isLoading = ref(false)
const comment = ref('')
const iframeClass = ref('w-full h-full')
const isIframeLoading = ref(true)
const iframeSrc = ref(route('grp.websites.header.preview', [route().params['website']]))


const onPickTemplate = (footer: Object) => {
    isModalOpen.value = false
    usedTemplates.value = footer
}

const onPublish = async (action: routeType, popover: Function) => {
    try {
        if (!action || !action.method || !action.name || !action.parameters) {
            throw new Error('Invalid action parameters')
        }
        isLoading.value = true
        const response = await axios[action.method](route(action.name, action.parameters), {
            comment: comment.value,
            layout: usedTemplates.value
        })
        popover.close()
    } catch (error) {
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


const autoSave = async (data: Object) => {
    try {
        const response = await axios.patch(
            route(props.autosaveRoute.name, props.autosaveRoute.parameters),
            { layout: data }
        )
    } catch (error: any) {
        console.error('error', error)
    }
}

const debouncedSendUpdate = debounce((data) => autoSave(data), 1000, { leading: false, trailing: true })

const setIframeView = (view: String) => {
    if (view === 'mobile') {
        iframeClass.value = 'w-[375px] h-[667px] mx-auto';
    } else if (view === 'tablet') {
        iframeClass.value = 'w-[768px] h-[1024px] mx-auto';
    } else {
        iframeClass.value = 'w-full h-full';
    }
}

const openFullScreenPreview = () => {
    window.open(iframeSrc.value, '_blank')
}

const handleIframeError = () => {
    console.error('Failed to load iframe content.');
}

watch(usedTemplates, (newVal) => {
    if (newVal) debouncedSendUpdate(newVal)
}, { deep: true })


</script>

<template>-

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-publish="{ action }">
            <Publish :isLoading="isLoading" :is_dirty="true" v-model="comment"
                @onPublish="(popover) => onPublish(action.route, popover)" />
        </template>
    </PageHeading>
    <!-- <pre>{{ usedTemplates  }}</pre> -->

    <div class="h-[84vh]  grid grid-flow-row-dense grid-cols-4">
        <div v-if="usedTemplates?.data" class="col-span-1 bg-[#F9F9F9] flex flex-col h-full border-r border-gray-300">
            <div class="py-2 px-2 font-bold text-lg">Form Editing</div>
            <SideEditor v-model="usedTemplates.data.footer" :bluprint="usedTemplates.data.bluprint" />
        </div>

        <div class="bg-gray-100 h-full" :class="usedTemplates ? 'col-span-3' : 'col-span-4'">
            <div  class="h-full w-full bg-white">
                <div v-if="usedTemplates" class="w-full h-full">
                    <div class="flex justify-between bg-slate-200 border border-b-gray-300">
                        <div class="flex">
                            <ScreenView @screenView="setIframeView" />
                            <div class="py-1 px-2 cursor-pointer" title="Desktop view" v-tooltip="'Preview'"
                                @click="openFullScreenPreview">
                                <FontAwesomeIcon :icon='faExternalLink' aria-hidden='true' />
                            </div>
                        </div>
                        <div class="flex">
                            <div class="py-1 px-2 cursor-pointer" title="template" v-tooltip="'Template'"
                                @click="isModalOpen = true">
                                <FontAwesomeIcon icon="fas fa-th-large" aria-hidden='true' />
                            </div>
                        </div>
                    </div>

                    <div v-if="isIframeLoading" class="flex justify-center items-center w-full h-64 p-12 bg-white">
                        <FontAwesomeIcon icon="fad fa-spinner-third" class="animate-spin w-6" aria-hidden="true" />
                    </div>
                    <iframe :src="iframeSrc" :title="props.title"
                        :class="[iframeClass, isIframeLoading ? 'hidden' : '']" @error="handleIframeError"
                        @load="isIframeLoading = false" />
                </div>
                <div v-else>
                    <EmptyState
                        :data="{ description: 'You need pick a template from list', title: 'Pick Footer Templates' }">
                        <template #button-empty-state>
                            <div class="mt-4 block">
                                <Button type="secondary" label="Templates" icon="fas fa-th-large"
                                    @click="isModalOpen = true"></Button>
                            </div>
                        </template>
                    </EmptyState>
                </div>
            </div>
        </div>
    </div>

    <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false">
        <BlockList :onPickBlock="onPickTemplate" :webBlockTypes="webBlockTypeCategories" scope="website" />
    </Modal>

</template>


<style scss></style>

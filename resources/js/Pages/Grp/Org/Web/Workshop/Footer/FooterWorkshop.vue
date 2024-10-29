<script setup lang="ts">
import { ref, watch, onMounted, IframeHTMLAttributes } from 'vue'
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import { SocketHeaderFooter } from '@/Composables/SocketWebBlock'
import { Switch } from '@headlessui/vue'
import Button from '@/Components/Elements/Buttons/Button.vue';
import Modal from '@/Components/Utils/Modal.vue'
import EmptyState from '@/Components/Utils/EmptyState.vue';
import SideEditor from '@/Components/Websites/SideEditor.vue';
import { notify } from "@kyvg/vue3-notification"
import axios from 'axios'
import { debounce, isArray } from 'lodash'
import Publish from '@/Components/Publish.vue'
import BlockList from '@/Components/Fulfilment/Website/Block/BlockList.vue'
import ScreenView from "@/Components/ScreenView.vue"


import { routeType } from "@/types/route"
import { PageHeading as TSPageHeading } from '@/types/PageHeading'

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faIcons, faMoneyBill, faUpload, faDownload } from '@fas';
import { faLineColumns } from '@far';
import { faExternalLink } from '@fal';
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faExternalLink, faLineColumns, faIcons, faMoneyBill, faUpload, faDownload)

const props = defineProps<{
    pageHead: TSPageHeading
    title: string
    data: {
        footer: Object
    }
    autosaveRoute: routeType
    webBlockTypes: Object
}>()

const previewMode = ref(false)
const isModalOpen = ref(false)
const usedTemplates = ref(isArray(props.data.footer) ? null : props.data.footer)
const tabsBar = ref(0)
const isLoading = ref(false)
const comment = ref('')
const iframeClass = ref('w-full h-full')
const isIframeLoading = ref(true)
console.log(route().params)
const iframeSrc = ref(
    route('grp.websites.footer.preview', [
        route().params['website'],
        {
            isInWorkshop: "true",
            organisation: route().params["organisation"],
            shop: route().params["shop"],
        }
    ]))
const socketLayout = SocketHeaderFooter(route().params['website']);


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


watch(previewMode, (newVal) => {
    sendToIframe({ key: 'isPreviewMode', value: newVal })
}, { deep: true })


const _iframe = ref<IframeHTMLAttributes | null>(null)
const sendToIframe = (data: any) => {
    _iframe.value?.contentWindow.postMessage(data, '*')
}

</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-publish="{ action }">
            <Publish :isLoading="isLoading" :is_dirty="true" v-model="comment"
                @onPublish="(popover) => onPublish(action.route, popover)" />
        </template>
    </PageHeading>

    <div class="h-[84vh] grid grid-flow-row-dense grid-cols-4">
        <div v-if="usedTemplates" class="col-span-1 bg-[#F9F9F9] flex flex-col h-full border-r border-gray-300">
            <div class="flex h-full">
                <div class="w-[10%] bg-slate-200 ">
                    <div v-for="(tab, index) in usedTemplates.blueprint"
                        class="py-2 px-3 cursor-pointer transition duration-300 ease-in-out transform hover:scale-105"
                        :title="tab.name" @click="tabsBar = index"
                        :class="[tabsBar == tab.key ? 'bg-gray-300/70' : 'hover:bg-gray-200/60']" v-tooltip="tab.name">
                        <FontAwesomeIcon :icon="tab.icon" :class="[tabsBar == index ? 'text-indigo-300' : '']"
                            aria-hidden='true' />
                    </div>
                </div>
                <div class="w-[90%]">
                    <SideEditor v-model="usedTemplates.data.fieldValue"
                        :bluprint="usedTemplates.blueprint[tabsBar].blueprint" />
                </div>
            </div>
        </div>

        <div class="bg-gray-100 h-full" :class="usedTemplates?.data ? 'col-span-3' : 'col-span-4'">
            <div class="h-full w-full bg-white">
                <div v-if="usedTemplates?.data" class="w-full h-full">
                    <div class="flex justify-between bg-slate-200 border border-b-gray-300">
                        <div class="flex">
                            <ScreenView @screenView="setIframeView" />
                            <div class="py-1 px-2 cursor-pointer" title="Desktop view" v-tooltip="'Preview'"
                                @click="openFullScreenPreview">
                                <FontAwesomeIcon :icon='faExternalLink' aria-hidden='true' />
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="text-xs" :class="[
                                previewMode ? 'text-slate-600' : 'text-slate-300'
                            ]">Preview</div>
                            <Switch @click="previewMode = !previewMode" :class="[
                                previewMode ? 'bg-slate-600' : 'bg-slate-300'
                            ]"
                                class="pr-1 relative inline-flex h-3 w-6 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
                                <span aria-hidden="true" :class="previewMode ? 'translate-x-3' : 'translate-x-0'"
                                    class="pointer-events-none inline-block h-full w-1/2 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out">
                                </span>
                            </Switch>

                            <div class="py-1 px-2 cursor-pointer" title="template" v-tooltip="'Template'"
                                @click="isModalOpen = true">
                                <FontAwesomeIcon icon="fas fa-th-large" aria-hidden='true' />
                            </div>
                        </div>
                    </div>

                    <div v-if="isIframeLoading" class="flex justify-center items-center w-full h-64 p-12 bg-white">
                        <FontAwesomeIcon icon="fad fa-spinner-third" class="animate-spin w-6" aria-hidden="true" />
                    </div>
                    <iframe :src="iframeSrc" :title="props.title" ref="_iframe"
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
        <BlockList :onPickBlock="onPickTemplate" :webBlockTypes="webBlockTypes" scope="website" />
    </Modal>
</template>


<style scss></style>

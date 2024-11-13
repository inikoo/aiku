<script setup lang="ts">
import { ref, watch, IframeHTMLAttributes, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import { Switch } from '@headlessui/vue'
import Button from '@/Components/Elements/Buttons/Button.vue';
import Modal from '@/Components/Utils/Modal.vue'
import EmptyState from '@/Components/Utils/EmptyState.vue';
import SideEditor from '@/Components/Workshop/SideEditor.vue';
import { notify } from "@kyvg/vue3-notification"
import axios from 'axios'
import { debounce, isArray } from 'lodash'
import Publish from '@/Components/Publish.vue'
import ScreenView from "@/Components/ScreenView.vue"
import Image from '@/Components/Image.vue'
import HeaderListModal from '@/Components/CMS/Fields/ListModal.vue'
import { trans } from "laravel-vue-i18n"
import { getBlueprint } from '@/Composables/getBlueprintWorkshop'
import { setIframeView } from "@/Composables/Workshop"
import ProgressSpinner from 'primevue/progressspinner';

import { routeType } from "@/types/route"
import { PageHeading as TSPageHeading } from '@/types/PageHeading'

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faIcons, faMoneyBill, faUpload, faDownload, faThLarge } from '@fas';
import { faLineColumns } from '@far';
import { faExternalLink } from '@fal';
import { library } from '@fortawesome/fontawesome-svg-core'


library.add(faExternalLink, faLineColumns, faIcons, faMoneyBill, faUpload, faThLarge)

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
const isLoading = ref(false)
const comment = ref('')
const iframeClass = ref('w-full h-full')
const saveCancelToken = ref<Function | null>(null)
const isIframeLoading = ref(true)
const iframeSrc = ref(
    route('grp.websites.footer.preview', [
        route().params['website'],
        {
            organisation: route().params["organisation"],
            shop: route().params["shop"],
        }
    ]))


const onPickTemplate = (footer: Object) => {
    isModalOpen.value = false
    usedTemplates.value = footer
    isIframeLoading.value = true
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
    router.patch(
        route(props.autosaveRoute.name, props.autosaveRoute.parameters),
        { layout: data },
        {
            onFinish: () => {
                saveCancelToken.value = null
                sendToIframe({ key: 'reload', value: {} })
                if(isIframeLoading.value){
                    isIframeLoading.value = false
                 /*    location.reload(); */
                }
            },
            onCancelToken: (cancelToken) => {
                saveCancelToken.value = cancelToken.cancel
            },
            onCancel: () => {
                console.log('The saving progress canceled.')
            },
            onError: (error) => {
                notify({
                    title: trans('Something went wrong.'),
                    text: error.message,
                    type: 'error',
                })
            },
            preserveScroll: true,
            preserveState: true,
        }
    )
}

const debouncedSendUpdate = debounce((data) => autoSave(data), 1000, { leading: false, trailing: true })

const handleIframeError = () => {
    console.error('Failed to load iframe content.');
}

watch(usedTemplates, (newVal) => {
    if (saveCancelToken.value) saveCancelToken.value()
    if (newVal) debouncedSendUpdate(newVal)
}, { deep: true })


watch(previewMode, (newVal) => {
    sendToIframe({ key: 'isPreviewMode', value: newVal })
}, { deep: true })


const _iframe = ref<IframeHTMLAttributes | null>(null)
const sendToIframe = (data: any) => {
    _iframe.value?.contentWindow.postMessage(data, '*')
}

const handleIframeMessage = (event: MessageEvent) => {
    if (event.origin !== window.location.origin) return;
    const { data } = event;

    if (data.key === 'autosave') {
        if (saveCancelToken.value) saveCancelToken.value()
        usedTemplates.value = data.value
    }
};

onMounted(() => {
    window.addEventListener('message', handleIframeMessage);
});

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
                <SideEditor v-model="usedTemplates.data.fieldValue" :blueprint="getBlueprint(usedTemplates.code)" />
            </div>
        </div>

        <div class="bg-gray-100 h-full" :class="usedTemplates?.data ? 'col-span-3' : 'col-span-4'">
            <div class="h-full w-full bg-white">
                <div v-if="usedTemplates?.data" class="w-full h-full">
                    <div class="flex justify-between bg-slate-200 border border-b-gray-300">
                        <div class="flex">
                            <ScreenView @screenView="(e) => iframeClass = setIframeView(e)" />
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
                                <FontAwesomeIcon :icon="faThLarge" aria-hidden='true' />
                            </div>
                        </div>
                    </div>

                    <div v-if="isIframeLoading" class="loading-overlay">
                        <ProgressSpinner />
                    </div>

                    <iframe :src="iframeSrc" :title="props.title" :class="[iframeClass]" @error="handleIframeError"
                        @load="isIframeLoading = false" ref="_iframe" />

                   <!--  <div v-if="isIframeLoading" class="flex justify-center items-center w-full h-64 p-12 bg-white">
                        <FontAwesomeIcon icon="fad fa-spinner-third" class="animate-spin w-6" aria-hidden="true" />
                    </div>
                    <iframe :src="iframeSrc" :title="props.title" ref="_iframe"
                        :class="[iframeClass, isIframeLoading ? 'hidden' : '']" @error="handleIframeError"
                        @load="isIframeLoading = false" /> -->
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
        <HeaderListModal :onSelectBlock="onPickTemplate"
            :webBlockTypes="webBlockTypes.data.filter((item) => item.component == 'footer')"
            :currentTopbar="usedTemplates">
            <template #image="{ block }">
                <div @click="() => onPickTemplate(block)"
                    class="min-h-16 w-full aspect-[2/1] overflow-hidden flex items-center bg-gray-100 justify-center border border-gray-300 hover:border-indigo-500 rounded cursor-pointer">
                    <div class="w-auto shadow-md">
                        <Image :src="block.screenshot" class="object-contain" />
                    </div>
                </div>
            </template>
        </HeaderListModal>
    </Modal>
</template>


<style scoped lang="scss">
:deep(.loading-overlay) {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.8);
    z-index: 1000;
}

:deep(.spinner) {
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top: 4px solid #3498db;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>

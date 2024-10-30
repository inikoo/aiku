<script setup lang="ts">
import { ref, watch, computed, toRaw, onMounted, Component, IframeHTMLAttributes, provide  } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import Button from '@/Components/Elements/Buttons/Button.vue'
import Modal from '@/Components/Utils/Modal.vue'
import EmptyState from '@/Components/Utils/EmptyState.vue'
import SideEditor from '@/Components/Websites/SideEditor.vue'
import { notify } from "@kyvg/vue3-notification"
import Publish from '@/Components/Publish.vue'
import { debounce } from 'lodash'
import ScreenView from "@/Components/ScreenView.vue"
import HeaderListModal from '@/Components/CMS/Website/Headers/HeaderListModal.vue'

import { routeType } from "@/types/route"
import { PageHeading as TSPageHeading } from '@/types/PageHeading'

import { faPresentation, faCube, faText, faPaperclip, faExternalLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faHeading, faHeart, faSignIn } from '@far'
import { faBrowser } from '@fal'

import { trans } from 'laravel-vue-i18n'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue';
import Toggle from '@/Components/Pure/Toggle.vue'

library.add(faBrowser, faPresentation, faCube, faText, faHeart, faPaperclip)

const props = defineProps<{
    pageHead: TSPageHeading
    title: string
    uploadImageRoute: routeType
    data: {
        data : {
            header : Object,
            topBar : Object
        }
    }
    autosaveRoute: routeType
    web_block_types: {}
    route_list: {
        upload_image: routeType
        uploaded_images_list: routeType
        stock_images_list: routeType
    }
}>()

provide('route_list', props.route_list)
const usedTemplates = ref({ 
    header : props.data.data.header,
    topBar : props.data.data.topBar
})
const isLoading = ref(false)
const comment = ref('')
const iframeClass = ref('w-full h-full')
const isIframeLoading = ref(true)
const iframeSrc = ref(route('grp.websites.header.preview', [route().params['website']]))
const tabs = [
    {
        label: "TopBars settings",
        componentName: "topbar",
        key: 'topBar',
        icon: faSignIn,
    },
    {
        label: "Website header",
        componentName: "header",
        key: 'header',
        icon: faHeading,
    }
]
const keySidebar = ref(0)
const selectedTab = ref(tabs[0])
const saveCancelToken = ref<Function | null>(null)

const onSelectBlock = (selectedBlock: object) => {
    const selectedKey = selectedTab.value.key;
    const currentTemplate = toRaw(usedTemplates.value[selectedKey]);
    const newTemplate = { ...toRaw(selectedBlock) };

    newTemplate.data.fieldValue = {
        ...currentTemplate?.data?.fieldValue,
        ...newTemplate?.data?.fieldValue
    };

    usedTemplates.value[selectedKey] = newTemplate;
    keySidebar.value++;
    isModalOpen.value = false;
}

// Method: Publish
const publishCancelToken = ref<{cancel: Function} | null>(null)
const onPublish = async (action: routeType, popover: Function) => {
    router[action.method || 'post'](
        route(action.name, action.parameters),
        {
            comment: comment.value,
            layout: usedTemplates.value
        },
        {
            onStart: () => isLoading.value = true,
            onCancelToken: (cancelToken) => publishCancelToken.value = cancelToken,
            onFinish: () => {
                isLoading.value = true
                publishCancelToken.value = null
            },
            onError: (error) => {
                notify({
                    title: trans('Something went wrong.'),
                    text: error.message,
                    type: 'error',
                })
            }
        }
    )

    // try {
    //     // Ensure action is defined and has necessary properties
    //     if (!action || !action.method || !action.name || !action.parameters) {
    //         throw new Error('Invalid action parameters')
    //     }

    //     isLoading.value = true

    //     // Make sure route and axios are defined and used correctly
    //     const response = await axios[action.method](route(action.name, action.parameters), {
    //         comment: comment.value,
    //         layout: usedTemplates.value
    //     })
    //     popover.close()
    // } catch (error) {
    //     // Ensure the error is logged properly
    //     console.error('Error:', error)

    //     // Ensure the error notification is user-friendly
    //     const errorMessage = error.response?.data?.message || error.message || 'Unknown error occurred'
    //     notify({
    //         title: 'Something went wrong.',
    //         text: errorMessage,
    //         type: 'error',
    //     })
    // } finally {
    //     // Ensure loading state is updated
    //     isLoading.value = false
    // }
}

// Method: auto save
const isLoadingSave = ref(false)
const onProgress = ref(false)
const autoSave = async (data: {}) => {
    router.patch(
        route(props.autosaveRoute.name, props.autosaveRoute.parameters),
        { layout: data },
        {
            onStart: () => isLoadingSave.value = true,
            onFinish: () => {
                isLoadingSave.value = false,
                saveCancelToken.value = null
            },
            onProgress: (progress) => {
                onProgress.value = progress
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


// If fieldvalue have changes, then auto save
watch(usedTemplates, (newVal) => {
    if (newVal) {
        // If still on progress saving, cancel the save
        if (saveCancelToken.value) {
            saveCancelToken.value()
        }

        debouncedSendUpdate(toRaw(newVal))
    }
}, { deep: true })


const selectedWebBlock = computed(() => {
    return props.web_block_types.filter(item => item.data.component === selectedTab.value.componentName)
})


const isModalOpen = ref(false)
onMounted(() => {
    window.addEventListener('message', (event) => {
        if (event.data === 'openModalBlockList') {
            isModalOpen.value = true
        }
    })
})

const isPreviewLoggedIn = ref(false)
// Section Iframe
const _iframe = ref<IframeHTMLAttributes | null>(null)
const sendToIframe = (data: any) => {
    _iframe.value?.contentWindow.postMessage(data, '*')
}

</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #mainIcon v-if="isLoadingSave">
            <LoadingIcon size="sm" />
        </template>

        <template #button-publish="{ action }">
            <Publish
                v-model="comment"
                :isLoading="isLoading || isLoadingSave"
                :is_dirty="true"
                @onPublish="(popover) => onPublish(action.route, popover)"
            />
        </template>
    </PageHeading>
    
    <div class="h-[84vh] flex">
        <div v-if="usedTemplates" class="col-span-2 bg-[#F9F9F9] flex flex-col h-full border-r border-gray-300">
            <!-- Section: Side editor -->
            <div class="flex h-full w-96">
                <div class="min-w-fit w-[10%] bg-slate-200 ">
                    <div v-for="(tab, index) in tabs"
                        class="py-2 px-3 cursor-pointer"
                        :title="tab.label" @click="selectedTab = tab"
                        :class="[selectedTab.key == tab.key ? 'bg-indigo-500 text-white' : 'hover:bg-gray-200/60']"
                        v-tooltip="tab.label">
                        <FontAwesomeIcon
                            :icon="tab.icon"
                            aria-hidden='true' />
                    </div>
                </div>
                
                <div class="w-full py-2 px-3 overflow-y-auto">
                    <div class="sticky top-0 bg-gray-50 z-20 text-lg font-semibold flex items-center justify-between gap-3 border-b border-gray-300">
                        <div class="flex items-center gap-3">
                            <FontAwesomeIcon :icon="selectedTab.icon" aria-hidden="true" />
                            <span>{{ selectedTab.label }}</span>
                        </div>

                        <div class="py-1 px-2 cursor-pointer" title="template" v-tooltip="'Template'"
                            @click="isModalOpen = true">
                            <FontAwesomeIcon icon="fas fa-th-large" aria-hidden='true' />
                        </div>
                    </div>

                    <!-- <pre>{{ usedTemplates?.[selectedTab.key].blueprint }}</pre> -->

                    <SideEditor
                        v-if="usedTemplates?.[selectedTab.key]?.data.fieldValue"
                        :key="keySidebar"
                        v-model="usedTemplates[selectedTab.key].data.fieldValue"
                        :bluprint="usedTemplates[selectedTab.key].blueprint" 
                        :uploadImageRoute="uploadImageRoute" 
                    />
                </div>
            </div>
        </div>


        <div :class="usedTemplates ? 'col-span-8' : 'col-span-10'" class="w-full">
            <div v-if="usedTemplates" class="h-full w-full bg-white">
                <!-- Section: Screenview -->
                <div class="flex justify-between max-w-7xl mx-auto bg-slate-200 border border-b-gray-300 pr-6">
                    <div class="flex">
                        <ScreenView @screenView="setIframeView" />
                        <div class="py-1 px-2 cursor-pointer" title="Desktop view" v-tooltip="'Preview'"
                            @click="openFullScreenPreview">
                            <FontAwesomeIcon :icon='faExternalLink' aria-hidden='true' />
                        </div>
                    </div>

                    <div class="flex items-center gap-x-2">
                        <span :class="!isPreviewLoggedIn ? 'text-gray-600' : 'text-gray-400'">Logged out</span>
                        <Toggle v-model="isPreviewLoggedIn" @update:modelValue="(newVal) => sendToIframe({key: 'isPreviewLoggedIn', value: newVal})" />
                        <span :class="isPreviewLoggedIn ? 'text-gray-600' : 'text-gray-400'">Logged in</span>
                    </div>
                </div>

                <div v-if="isIframeLoading" class="flex justify-center items-center w-full h-64 p-12 bg-white">
                    <FontAwesomeIcon icon="fad fa-spinner-third" class="animate-spin w-6" aria-hidden="true" />
                </div>

                <!-- Workshop Preview -->
                <iframe
                    ref="_iframe"
                    :src="iframeSrc + '?isInWorkshop=true'"
                    :title="props.title"
                    :class="[iframeClass, isIframeLoading ? 'hidden' : '']"
                    @error="handleIframeError"
                    @load="isIframeLoading = false"
                />

            </div>

            <section v-else>
                <EmptyState
                    :data="{ description: 'You need pick a template from list', title: 'Pick Header Templates' }">
                    <template #button-empty-state>
                        <div class="mt-4 block">
                            <Button
                                type="secondary"
                                label="Templates"
                                icon="fas fa-th-large"
                                @click="isModalOpen = true"
                            />
                        </div>
                    </template>
                </EmptyState>
            </section>
        </div>
    </div>

    <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false">
        <HeaderListModal 
            :onSelectBlock
            :webBlockTypes="selectedWebBlock"
            :currentTopbar="usedTemplates.topBar"
        />
    </Modal>
</template>


<style lang="scss" scoped></style>

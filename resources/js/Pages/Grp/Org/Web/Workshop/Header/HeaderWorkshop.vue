<script setup lang="ts">
import { ref, watch, computed, toRaw  } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import Button from '@/Components/Elements/Buttons/Button.vue'
import Modal from '@/Components/Utils/Modal.vue'
import EmptyState from '@/Components/Utils/EmptyState.vue'
import SideEditor from '@/Components/Websites/SideEditor.vue'
import { notify } from "@kyvg/vue3-notification"
import Publish from '@/Components/Publish.vue'
import axios from 'axios'
import { debounce } from 'lodash'
import ScreenView from "@/Components/ScreenView.vue"
import BlockList from '@/Components/Fulfilment/Website/Block/BlockList.vue'
import TopbarList from '@/Components/Websites/Topbar/TopbarList.vue'
import HeaderListModal from '@/Components/Websites/Header/HeaderListModal.vue'

import { routeType } from "@/types/route"
import { PageHeading as TSPageHeading } from '@/types/PageHeading'

import { faPresentation, faCube, faText, faPaperclip, faExternalLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faHeart } from '@far'
import { faBrowser } from '@fal'

import { trans } from 'laravel-vue-i18n'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue';

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

}>()

const isModalOpen = ref(false)
const usedTemplates = ref({ 
    header : props.data.data.header,
    topBar : props.data.data.topBar
})
const isLoading = ref(false)
const comment = ref('')
const iframeClass = ref('w-full h-full')
const isIframeLoading = ref(true)
const iframeSrc = ref(route('grp.websites.header.preview', [route().params['website']]))
const loginMode = ref(true)
const tabs = [
    {
        label: "Topbar settings",
        componentName: "topbar",
        key: 'topBar',
        icon: faPresentation,
    },
    {
        label: "Website header",
        componentName: "header",
        key: 'header',
        icon: faPresentation,
    }
]
const keySidebar = ref(0)
const selectedTab = ref(tabs[0])
const saveCancelToken = ref<Function | null>(null)

const onSelectBlock = (selectedBlock: object) => {
    const oldTemplate = {...toRaw(usedTemplates.value[selectedTab.value.key])}
    
    usedTemplates.value[selectedTab.value.key] = selectedBlock
    
    usedTemplates.value[selectedTab.value.key].data.fieldValue = {
        ...usedTemplates.value[selectedTab.value.key].data.fieldValue,
        ...oldTemplate.data.fieldValue
    }

    keySidebar.value++
    isModalOpen.value = false
}

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

const isLoadingSave = ref(false)
const onProgress = ref(false)
const autoSave = async (data: object) => {
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
                console.log('on cancel')
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

        debouncedSendUpdate(newVal)
    }
}, { deep: true })


const selectedWebBlock = computed(() => {
    return props.web_block_types.filter(item => item.data.component === selectedTab.value.componentName)
    
    // const filteredData = { ...props.web_block_types };

    
    // if (Array.isArray(filteredData.data)) {
    //     filteredData.data = filteredData.data.filter(item => item.name === tabsBar.value.label)
    // } else {
    //     filteredData.data = [];
    // }

    // return filteredData;
})


</script>

<template>
    <Head :title="capitalize(title)" />

    <!-- {{ onProgress }}
    <div @click="() => saveCancelToken ? saveCancelToken() : autoSave(usedTemplates)">{{ saveCancelToken ? 'xxx' : 'gfgf' }} foidsa jfoidsajfodsjafdsa</div>
    {{ saveCancelToken }} -->

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
                
                <div class="w-full py-2 px-3">
                    <div class="text-lg font-semibold flex items-center justify-between gap-3 border-b border-gray-300">
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
                <div class="flex justify-between bg-slate-200 border border-b-gray-300">
                    <div class="flex">
                        <ScreenView @screenView="setIframeView" />
                        <div class="py-1 px-2 cursor-pointer" title="Desktop view" v-tooltip="'Preview'"
                            @click="openFullScreenPreview">
                            <FontAwesomeIcon :icon='faExternalLink' aria-hidden='true' />
                        </div>
                    </div>
                </div>

                <div v-if="isIframeLoading" class="flex justify-center items-center w-full h-64 p-12 bg-white">
                    <FontAwesomeIcon icon="fad fa-spinner-third" class="animate-spin w-6" aria-hidden="true" />
                </div>

                <iframe
                    :src="iframeSrc"
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


    <!-- <Modal :isOpen="isOpenModalTopbarList" @onClose="isOpenModalTopbarList = false">
        <TopbarList 
            :onSelectTopbar
            :topbarList="selectedWebBlock"
        />
    </Modal> -->
</template>


<style lang="scss" scoped></style>

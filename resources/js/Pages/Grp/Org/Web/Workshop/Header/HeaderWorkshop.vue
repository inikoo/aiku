<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
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

import { routeType } from "@/types/route"
import { PageHeading as TSPageHeading } from '@/types/PageHeading'

import { faPresentation, faCube, faText, faPaperclip, faExternalLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faHeart } from '@far'
import { faBrowser } from '@fal'


import Accordion from 'primevue/accordion'
import AccordionPanel from 'primevue/accordionpanel'
import AccordionHeader from 'primevue/accordionheader'
import AccordionContent from 'primevue/accordioncontent'
import { trans } from 'laravel-vue-i18n'
import BackgroundProperty from '@/Components/Websites/Fields/Properties/BackgroundProperty.vue'

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
    webBlockTypeCategories: Object

}>()

console.log(props)
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
const debouncedSendUpdate = debounce((data) => autoSave(data), 1000, { leading: false, trailing: true })
const tabs = [
    /* {
        label: "Body",
        key: 'body',
        icon: faPresentation,
    }, */
    {
        label: "Top Menu",
        key: 'topBar',
        icon: faPresentation,
    },
    {
        label: "Header",
        key: 'header',
        icon: faPresentation,
    }
]
const tabsBar = ref(tabs[0])

const onPickTemplate = (data: object) => {
    usedTemplates.value[tabsBar.value.key] = data.data
}

const onPublish = async (action: routeType, popover: Function) => {
    try {
        // Ensure action is defined and has necessary properties
        if (!action || !action.method || !action.name || !action.parameters) {
            throw new Error('Invalid action parameters')
        }

        isLoading.value = true

        // Make sure route and axios are defined and used correctly
        const response = await axios[action.method](route(action.name, action.parameters), {
            comment: comment.value,
            layout: usedTemplates.value
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

const autoSave = async (data: object) => {
    try {
        const response = await axios.patch(
            route(props.autosaveRoute.name, props.autosaveRoute.parameters),
            { layout: data }
        )
    } catch (error: any) {
        notify({
            title: 'Something went wrong.',
            text: error.message,
            type: 'error',
        })
    }
}

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

const openedAccordion = ref<string | null>(null)

const isOpenModalTopbarList = ref(false)
const topbar = ref({
    template: null,
    properties: {
        background: {
            type: 'color',
            color: '#ff000054',
            image: {
                original: 'string'
            }
        }
    }
})
const topbarList = [
    {
        code: 'codetopbar1',
        name: 'Topbar Universe',
        image: 'https://uploads.commoninja.com/searchengine/wordpress/zidi-topbar-menu.png'
    },
    {
        code: 'codetopbar2',
        name: 'Topbar Astronauts',
        image: 'https://cdn-icons-png.flaticon.com/256/3596/3596219.png'
    }
]
const onSelectTopbar = (xxx) => {
    // console.log('zxcxz')
    topbar.value.template = xxx
    isOpenModalTopbarList.value = false
}
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-publish="{ action }">
            <Publish
                v-model="comment"
                :isLoading="isLoading"
                :is_dirty="true"
                @onPublish="(popover) => onPublish(action.route, popover)"
            />
        </template>
    </PageHeading>

    <div class="h-[84vh] grid grid-flow-row-dense grid-cols-10">
        <div v-if="usedTemplates" class="col-span-2 bg-[#F9F9F9] flex flex-col h-full border-r border-gray-300">
            <Accordion>
                <AccordionPanel value="topbar" @click="openedAccordion = 'topbar'">
                    <AccordionHeader>
                        <div class="font-bold text-lg">Topbar Settings</div>
                    </AccordionHeader>
                    
                    <AccordionContent>
                        <div class="bg-white mt-[0px] ">
                            <div class="py-2">
                                <div>
                                    {{ topbar.template }}
                                </div>
                                <Button @click="() => isOpenModalTopbarList = true" type="tertiary" :label="trans('Select topbar template')" full />
                            </div>

                            <div v-if="topbar?.properties.background" class="border-t border-gray-300  pb-3">
                                <div class="my-2 text-gray-500 text-xs font-semibold">{{ trans('Background') }}</div>

                                <BackgroundProperty v-model="topbar.properties.background" />
                            </div>
                        </div>
                    </AccordionContent>
                </AccordionPanel>
            </Accordion>

            <div class="flex h-full">
                <div class="w-[10%] bg-slate-200 ">
                    <div v-for="(tab, index) in tabs"
                        class="py-2 px-3 cursor-pointer transition duration-300 ease-in-out transform hover:scale-105"
                        :title="tab.label" @click="tabsBar = tab"
                        :class="[tabsBar.key == tab.key ? 'bg-gray-300/70' : 'hover:bg-gray-200/60']"
                        v-tooltip="tab.label">
                        <FontAwesomeIcon :icon="tab.icon" :class="[tabsBar.key == tab.key ? 'text-indigo-300' : '']"
                            aria-hidden='true' />
                    </div>
                </div>
                <div class="w-[90%] py-2 px-3">
                    <div class="text-lg font-semibold flex items-center justify-between gap-3 border-b border-gray-300">
                        <div class="flex items-center gap-3">
                            <FontAwesomeIcon :icon="tabsBar.icon" aria-hidden="true" />
                            <span>{{ tabsBar.label }}</span>
                        </div>

                        <div class="py-1 px-2 cursor-pointer" title="template" v-tooltip="'Template'"
                            @click="isModalOpen = true">
                            <FontAwesomeIcon icon="fas fa-th-large" aria-hidden='true' />
                        </div>
                    </div>
                    <SideEditor 
                        v-model="usedTemplates[tabsBar.key].data"
                        :bluprint="usedTemplates[tabsBar.key].bluprint" 
                        :uploadImageRoute="uploadImageRoute" 
                    />
                </div>
            </div>
        </div>


        <div :class="usedTemplates ? 'col-span-8' : 'col-span-10'">
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
                <iframe :src="iframeSrc" :title="props.title" :class="[iframeClass, isIframeLoading ? 'hidden' : '']"
                    @error="handleIframeError" @load="isIframeLoading = false" />

            </div>
            <section v-else>
                <EmptyState
                    :data="{ description: 'You need pick a template from list', title: 'Pick Header Templates' }">
                    <template #button-empty-state>
                        <div class="mt-4 block">
                            <Button type="secondary" label="Templates" icon="fas fa-th-large"
                                @click="isModalOpen = true"></Button>
                        </div>
                    </template>
                </EmptyState>
            </section>
        </div>
    </div>

    <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false">
        <BlockList 
            :onPickBlock="onPickTemplate" 
            :webBlockTypes="webBlockTypeCategories" 
            scope="website" 
        />
    </Modal>

    <Modal :isOpen="isOpenModalTopbarList" @onClose="isOpenModalTopbarList = false">
        <TopbarList 
            :onSelectTopbar
            :topbarList="topbarList"
        />
    </Modal>
</template>


<style lang="scss" scoped></style>

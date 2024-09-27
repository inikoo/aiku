<!--
  - Author: Artha <artha@aw-advantage.com>
  - Created: Thu, 26 Sep 2024 13:18:33 Central Indonesia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { getComponent } from '@/Components/Fulfilment/Website/BlocksList'
import { ref, onMounted, onUnmounted, inject } from 'vue'
import WebPreview from "@/Layouts/WebPreview.vue";
import axios from 'axios'
import debounce from 'lodash/debounce'
import EmptyState from "@/Components/Utils/EmptyState.vue"
import { webBlock as socketWeblock } from '@/Composables/SocketWebBlock'
import Notification from '@/Components/Utils/Notification.vue'
import IrisHeader from '@/Layouts/Iris/Header.vue'
import NavigationMenu from '@/Layouts/Iris/NavigationMenu.vue'
import Footer from '@/Layouts/Iris/Footer.vue'
import { usePage } from '@inertiajs/vue3'
import { layoutStructure } from '@/Composables/useLayoutStructure'
import { data as headerData, bluprintForm as bluprintFormHeader } from '@/Components/Websites/Header/HeaderTemplates/Header1/descriptor'
import { data as footerData, bluprintForm as bluprintFormFooter } from '@/Components/Websites/Footer/FooterTemplates/Footer1/descriptor'
import { navigation as navigationData } from '@/Components/Websites/Menu/Descriptor'
import { useColorTheme } from '@/Composables/useStockList'

import { Root, Daum } from '@/types/webBlockTypes'
import { Root as RootWebpage, WebBlock } from '@/types/webpageTypes'


defineOptions({ layout: WebPreview })
const props = defineProps<{
    webpage: RootWebpage
    webBlockTypes: Root
}>()

const data = ref({ ...props.webpage })
const debouncedSendUpdate = debounce((block) => sendBlockUpdate(block), 1000, { leading: false, trailing: true })

const layout = inject('layout', layoutStructure)



const header = usePage().props?.iris?.header ? usePage().props?.iris?.header : { key: "header1", data: headerData, bluprint: bluprintFormHeader }
const footer = usePage().props?.iris?.footer ? usePage().props?.iris?.footer : { key: "footer1", data: footerData, bluprint: bluprintFormFooter }
const navigation = usePage().props?.iris?.menu ? usePage().props?.iris?.menu : { key: "menu1", data: navigationData }
const colorThemed = usePage().props?.iris?.color ? usePage().props?.iris?.color : { color: [...useColorTheme[2]] }

const onUpdatedBlock = (block: Daum) => {
    debouncedSendUpdate(block)
}

const sendBlockUpdate = async (block: WebBlock) => {
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
const socketConnection = socketWeblock(props.webpage.slug);
onMounted(() => {
    // Subscribe to the private Echo channel
    const channel = window.Echo.private(`webpage.${props.webpage.slug}.preview`)
        .listen('.WebpagePreview', (eventData) => {
            if (eventData) {
                data.value = { ...eventData.webpage }
            } else {
                console.error('No data received from event')
            }
        })
})

onUnmounted(() => {
    socketConnection.actions.unsubscribe();
});

</script>


<template>

    <div class="container max-w-7xl mx-auto shadow-xl">
        <IrisHeader :data="header" :colorThemed="colorThemed" />

        <!-- Section: Navigation Tab -->
        <NavigationMenu :data="navigation" :colorThemed="colorThemed" />

        <div class="relative">
            <div class="container max-w-7xl mx-auto">
                <div class="h-full overflow-auto w-full ">
                    <div v-if="data.layout.web_blocks?.length">
                        <TransitionGroup tag="div" name="zzz" class="relative">
                            <section v-for="(activityItem, activityItemIdx) in data.layout.web_blocks" :style="{
                                paddingTop: `${activityItem?.web_block?.layout?.data?.blockLayout?.paddingTop?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.paddingTop?.unit}`,
                                paddingBottom: `${activityItem?.web_block?.layout?.data?.blockLayout?.paddingBottom?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.paddingBottom?.unit}`,
                                paddingRight: `${activityItem?.web_block?.layout?.data?.blockLayout?.paddingRight?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.paddingRight?.unit}`,
                                paddingLeft: `${activityItem?.web_block?.layout?.data?.blockLayout?.paddingLeft?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.paddingLeft?.unit}`,
                                marginTop: `${activityItem?.web_block?.layout?.data?.blockLayout?.marginTop?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.marginTop?.unit}`,
                                marginBottom: `${activityItem?.web_block?.layout?.data?.blockLayout?.marginBottom?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.marginBottom?.unit}`,
                                marginRight: `${activityItem?.web_block?.layout?.data?.blockLayout?.marginRight?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.marginRight?.unit}`,
                                marginLeft: `${activityItem?.web_block?.layout?.data?.blockLayout?.marginLeft?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.marginLeft?.unit}`
                            }" :key="activityItem.id" class="w-full">
                                <component :is="getComponent(activityItem?.web_block?.layout?.data?.component)"
                                    :key="activityItemIdx" :webpageData="webpage" v-bind="activityItem"
                                    v-model="activityItem.web_block.layout.data.fieldValue" :isEditable="true"
                                    @autoSave="() => onUpdatedBlock(activityItem)" />
                            </section>
                        </TransitionGroup>

                    </div>
                    <div v-else>
                        <EmptyState
                            :data="{ title: 'Pick First Block For Your Website', description: 'Pick block from list' }">
                        </EmptyState>
                    </div>
                </div>
            </div>
        </div>

        <Footer :data="footer" :colorThemed="colorThemed" />
    </div>


</template>

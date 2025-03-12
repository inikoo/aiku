<!--
  - Author: Artha <artha@aw-advantage.com>
  - Created: Thu, 26 Sep 2024 13:18:33 Central Indonesia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { getComponent } from '@/Composables/getWorkshopComponents';
import { ref, onMounted, toRaw } from 'vue'
import WebPreview from "@/Layouts/WebPreview.vue"
import EmptyState from "@/Components/Utils/EmptyState.vue"
import { sendMessageToParent } from '@/Composables/Workshop'
import { router } from '@inertiajs/vue3'
import "@/../css/Iris/editor.css"

import { Root as RootWebpage } from '@/types/webpageTypes'
import { trans } from 'laravel-vue-i18n'


defineOptions({ layout: WebPreview })
const props = defineProps<{
    webpage?: RootWebpage
    header: {
        data: {}
    }
    footer: {
        footer: {}
    }
    navigation: {
        menu: {}
    }
    layout: {
    }
}>()

const isPreviewLoggedIn = ref(false)
const isPreviewMode = ref(false)
const activeBlock = ref(null)

const showWebpage = (activityItem) => {
    if (activityItem?.web_block?.layout && activityItem.show) {
        if (isPreviewLoggedIn.value && activityItem.visibility.in) return true
        else if (!isPreviewLoggedIn.value && activityItem.visibility.out) return true
        else return false
    } else return false
}

const updateData = (newVal) => {
    sendMessageToParent('autosave', newVal)
}

onMounted(() => {
    window.addEventListener('message', (event) => {
        if (event.data.key === 'isPreviewLoggedIn') isPreviewLoggedIn.value = event.data.value
        if (event.data.key === 'isPreviewMode') isPreviewMode.value = event.data.value
        if (event.data.key === 'activeBlock') {
            activeBlock.value = event.data.value
            const blockElement = document.querySelector(`[data-block-id="${event.data.value}"]`);
            if (blockElement) {
                blockElement.scrollIntoView({ behavior: "smooth", block: "center" });
            }
        }
        if (event.data.key === 'reload') {
            router.reload({
                only: ['footer', 'header', 'webpage'],
                onSuccess: () => { }
            });
        }
    });
});


</script>


<template>
    <div class="editor-class">
        <div class="shadow-xl" :class="layout?.layout === 'fullscreen' ? 'w-full' : 'container max-w-7xl mx-auto'">
            <div v-if="webpage">
                <div v-if="webpage?.layout?.web_blocks?.length">
                    <TransitionGroup tag="div" name="list" class="relative">
                        <template v-for="(activityItem, activityItemIdx) in webpage.layout.web_blocks"
                            :key="activityItem.id">
                            <section class="w-full min-h-[50px]" :data-block-id="activityItemIdx"
                                v-show="showWebpage(activityItem)" :class="{
                                    'hover-dashed': true,
                                    'ring-2 ring-[#62748E] ring-offset-2': activeBlock === activityItemIdx
                                }" @click="() => sendMessageToParent('activeBlock', activityItemIdx)">
                                <component class="w-full" :is="getComponent(activityItem.type)" :webpageData="webpage"
                                    :blockData="activityItem" @autoSave="() => updateData(activityItem)"
                                    v-model="activityItem.web_block.layout.data.fieldValue" />
                            </section>
                        </template>
                    </TransitionGroup>
                </div>
                <EmptyState v-else :data="{
                    title: trans('Pick First Block For Your Website'),
                    description: trans('Pick block from list'),
                }" />
            </div>
        </div>
    </div>
</template>



<style lang="scss">
.hover-dashed {
    @apply relative;

    &::after {
        content: "";
        @apply absolute inset-0 hover:bg-gray-200/30 border border-transparent hover:border-white/80 border-dashed cursor-pointer;
    }
}
</style>
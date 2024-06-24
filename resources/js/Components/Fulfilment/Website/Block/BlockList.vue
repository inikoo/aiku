<script setup lang="ts">
import { faPresentation, faCube, faText, faImage, faImages, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import dataList from "../data/blogActivity.js"
import { cloneDeep } from 'lodash'
import Tabs from "@/Components/Navigation/Tabs.vue"
import { ref } from 'vue'
import { trans } from "laravel-vue-i18n"

library.add(faPresentation, faCube, faText, faImage, faImages, faPaperclip)
const props = defineProps<{
    onPickBlock: Function
    webBlockTypes: {
        data: []
    }
}>()

// Method: to count the types of the blocks 
const countTypes = () => {
    return dataList.block.reduce((acc, obj) => {
        const type = obj.type
        if (acc[type]) {
            acc[type]++
        } else {
            acc[type] = 1
        }
        return acc
    }, {})
}

const tabs = {
    all: {
        title: `All (${dataList.block.length})`,
        key: 'all',
    },
/*     text: {
        title: `Text (${countTypes().text || 0})`,
        key: 'text',
        icon: ['fal', 'text']
    },
    product: {
        title: `Product (${countTypes().product || 0})`,
        key: 'product',
        icon: ['fal', 'cube']
    },
    wowsbar: {
        title: `Wowsbar (${countTypes().wowsbar || 0})`,
        key: 'wowsbar',
        icon: ['fal', 'presentation']
    } */
}

const listBlocks = ref(cloneDeep(props.webBlockTypes.data))
const currentTab = ref(0)

/* const filter = (e: string) => {
    if (tabs[e].key != 'all') {
        const filterData = cloneDeep(dataList.block).filter((item) => item.type == tabs[e].key)
        listBlocks.value = filterData
    } else {
        listBlocks.value = cloneDeep(dataList.block)
    }
    currentTab.value = e
}
 */
</script>


<template>
    <div class="bg-white h-[500px]">
        <div class="px-4 pb-8 text-center sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight">Blocks</h1>
        </div>

        <div class="mb-4">
            <Tabs :current="currentTab" :navigation="tabs" @update:tab="(tabName: string) => filter(tabName)" />
        </div>

        <section aria-labelledby="products-heading" class="h-full mx-auto w-full sm:px-6 lg:px-8 overflow-y-auto">
            <TransitionGroup tag="div" name="zzz" class="relative grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-y-3 gap-x-4 overflow-y-auto overflow-x-hidden">
                <template v-if="listBlocks.length">
                    <div v-for="block in listBlocks" :key="block.code" @click="() => onPickBlock(block)"
                        class="group flex items-center gap-x-2 relative border border-gray-300 px-3 py-2 rounded cursor-pointer hover:bg-gray-100">
                        <div class="flex items-center justify-center">
                            <FontAwesomeIcon :icon='block.data.icon' class='' fixed-width aria-hidden='true' />
                        </div>
                        <h3 class="text-sm font-medium">
                            {{ block.name }}
                        </h3>
                    </div>
                </template>

                <div v-else class="text-center col-span-2 md:col-span-3 lg:col-span-4 text-gray-400">
                    {{ trans('No block in this category') }}
                </div>
            </TransitionGroup>
        </section>
    </div>
</template>


<style lang="scss">
</style>

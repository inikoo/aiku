<script setup lang="ts">
import { faPresentation, faCube, faText, faImage, faImages, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import dataList from "../data/blogActivity.js"
import  { cloneDeep } from 'lodash'
import Tabs from "@/Components/Navigation/Tabs.vue"
import { useTabChange } from "@/Composables/tab-change"
import { ref } from 'vue'

library.add(faPresentation, faCube, faText, faImage, faImages, faPaperclip );
const props = defineProps<{
    onPickBlock: Funcition,
    webBlockTypes: {
        data : Array
    }
}>();

const tabs = [
    {
        title : 'All',
        key : 'all',
        icon : []
    },
    {
        title : 'Text',
        key : 'text',
        icon : ['fal','text']
    },
    {
        title : 'Product',
        key : 'product',
        icon : ['fal','cube']
    },
    {
        title : 'Wowsbar',
        key : 'wowsbar',
        icon : ['fal','presentation']
    }
]
const list = ref(cloneDeep(dataList.block))
const currentTab = ref(0)

const filter = (e) => {
    if (tabs[e].key != 'all') {
        const filterData = cloneDeep(dataList.block).filter((item) => item.type == tabs[e].key)
        list.value = filterData
    } else {
        list.value = cloneDeep(dataList.block)
    }
    currentTab.value = e
}



</script>

<template>
    <div class="bg-white">
        <main class="pb-24">
            <div class="px-4 pb-16 text-center sm:px-6 lg:px-8">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900">Blocks</h1>
                <p class="mx-auto mt-4 max-w-xl text-base text-gray-500">
                    The secret to a tidy desk? Don't get rid of anything, just put it in really
                    really nice looking containers.
                </p>
            </div>


            <div class="mb-4">
                <Tabs :current="currentTab" :navigation="tabs" @update:tab="filter"/>
            </div>
          

            <section aria-labelledby="products-heading" class="mx-auto w-full sm:px-6 lg:px-8">
                <h2 id="products-heading" class="sr-only">Products</h2>

                <div class="-mx-px grid grid-cols-2 border-l border-gray-200 sm:mx-0 md:grid-cols-3 lg:grid-cols-4">
                    <div v-for="product in list" :key="product.id" @click="()=>onPickBlock(product)"
                        class="group relative border-b border-t border-r p-2">
                        <div class="w-32 h-32 overflow-hidden rounded-lg group-hover:opacity-75 mx-auto">
                            <font-awesome-icon :icon="product.icon" class="h-full w-full object-cover object-center" />
                        </div>
                        <div class="mt-2 text-center">
                            <h3 class="text-sm font-medium text-gray-900">
                                <a :href="product.href">
                                    <span aria-hidden="true" class="absolute inset-0"></span>
                                    {{ product.name }}
                                </a>
                            </h3>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<style lang="scss">
</style>

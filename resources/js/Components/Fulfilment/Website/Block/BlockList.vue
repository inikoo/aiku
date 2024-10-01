<script setup lang="ts">
import { ref } from 'vue';
import { faPresentation, faCube, faText, faImage, faImages, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { trans } from "laravel-vue-i18n"

import { Root, Daum } from "@/types/webBlockTypes"


library.add(faPresentation, faCube, faText, faImage, faImages, faPaperclip)
const props = defineProps<{
    onPickBlock: Function
    webBlockTypes: Root
}>();

// Define active item state
const active = ref<Daum>(props.webBlockTypes.data[0]);

// Function to set active item
const setActiveId = (value: Daum) => {
    active.value = value;
};

console.log(props)

</script>

<template>
    <div class="flex border rounded-xl">
        <nav class="w-1/5 bg-gray-100 p-4 rounded-l-lg" aria-label="Sidebar">
            <ul role="list" class="-mx-2 space-y-1">
                <li v-for="item in webBlockTypes.data" :key="item.id">
                    <span :class="[
                        item.id === active.id ? 'bg-gray-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600',
                        'group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6']" @click="setActiveId(item)">
                        {{ item.name }}
                    </span>
                </li>
            </ul>
        </nav>

        <div class="flex-1 p-4">
            <section aria-labelledby="products-heading" class="h-full mx-auto w-full sm:px-6 lg:px-8 overflow-y-auto">
                <TransitionGroup tag="div" name="zzz"
                    class="relative grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-y-3 gap-x-4 overflow-y-auto overflow-x-hidden">
                    <template v-if="active.webBlockTypes.length">
                        <div v-for="block in active.webBlockTypes" :key="block.code" @click="() => onPickBlock(block)"
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
    </div>
</template>

<style lang="scss" scoped>
/* You can add additional scoped styles here if needed */
</style>

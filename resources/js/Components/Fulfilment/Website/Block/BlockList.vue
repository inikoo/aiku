<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { faPresentation, faCube, faText, faImage, faImages, faPaperclip, faShoppingBasket, faStar, faHandHoldingBox, faBoxFull, faBars, faBorderAll, faLocationArrow } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { trans } from "laravel-vue-i18n"

import { Root, Daum } from "@/types/webBlockTypes"

library.add(faPresentation, faCube, faText, faImage, faImages, faPaperclip, faShoppingBasket, faStar, faHandHoldingBox, faBoxFull, faBars, faBorderAll, faLocationArrow)
const props = withDefaults(defineProps<{
    onPickBlock: Function
    webBlockTypes: Root
    scope?: String /* all|website|webpage */
}>(), {
    scope: "all",
})

const data = ref<Daum[]>([])

// Define active item state
const active = ref<Daum>(props.webBlockTypes.data[0]);

// Function to set active item
const setActiveId = (value: Daum) => {
    active.value = value;
};

// Filter webBlockTypes based on scope and save in data
onMounted(() => {
    if (props.scope === 'all') {
        data.value = props.webBlockTypes.data; // Use all items if scope is 'all'
    } else {
        // Filter based on scope (e.g., 'website', 'webpage', etc.)
        data.value = props.webBlockTypes.data.filter(item => item.scope === props.scope);
    }

    active.value = data.value[0] || null; // Set default active item from filtered data
});

</script>

<template>
    <div class="flex border rounded-xl overflow-hidden">
        <nav class="w-1/5 bg-gray-100 py-4" aria-label="Sidebar">
            <ul role="list" class="space-y-1">

                <li v-for="item in data" :key="item.id"
                    :class="[item.id === active.id ? 'bg-white text-indigo-600' : 'hover:bg-white/50 hover:text-indigo-600']"
                    @click="setActiveId(item)"
                    class="group flex items-center gap-x-2 p-3 text-sm font-semibold cursor-pointer">
                    <FontAwesomeIcon v-if="item.icon" :icon='item.icon' class='text-sm text-gray-400' fixed-width
                        aria-hidden='true' />
                    {{ item.name }}
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

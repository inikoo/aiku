<script setup lang="ts">

import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import dataList from "../data/blogActivity.js"
import PureInput from "@/Components/Pure/PureInput.vue"
import Popover from '@/Components/Popover.vue'
import { ref } from "vue"

// Define props
const props = defineProps<{
    modelValue: any
}>()

// Dummy sections with conditional anchor handling
const sections = ref([
    {
        title: 'Incense Packaging',
        sub_title: 'Choose from a range of eco-friendly packaging options',
        items: [
            {
                name: 'Eco Box 1',
                details: '100% recycled material',
                type: 'anchor', // This item will be clickable
                webpage_key: 'incense_packaging_eco_box_1',
                category_key: 'packaging',
                webpage_code: 'IP001',
                category_code: 'PK001',
                image_website: 'https://via.placeholder.com/300',
                number_products: 10,
                link: 'https://example.com/eco-box-1' // Link for anchor type
            },
            {
                name: 'Eco Box 2',
                details: 'Bio-degradable and sustainable',
                type: 'non_anchor', // This item will not be clickable
                webpage_key: 'incense_packaging_eco_box_2',
                category_key: 'packaging',
                webpage_code: 'IP002',
                category_code: 'PK002',
                image_website: 'https://via.placeholder.com/300',
                number_products: 15
            },
            {
                name: 'Eco Box 2',
                details: 'Bio-degradable and sustainable',
                type: 'non_anchor', // This item will not be clickable
                webpage_key: 'incense_packaging_eco_box_2',
                category_key: 'packaging',
                webpage_code: 'IP002',
                category_code: 'PK002',
                image_website: 'https://via.placeholder.com/300',
                number_products: 15
            },
            {
                name: 'Eco Box 2',
                details: 'Bio-degradable and sustainable',
                type: 'non_anchor', // This item will not be clickable
                webpage_key: 'incense_packaging_eco_box_2',
                category_key: 'packaging',
                webpage_code: 'IP002',
                category_code: 'PK002',
                image_website: 'https://via.placeholder.com/300',
                number_products: 15
            }
        ]
    }
])

</script>

<template>
    <div id="sections-container" class="space-y-8">
        <!-- Iterate over sections dynamically -->
        <div v-for="(section, index) in sections" :key="index" class="section">
            <div class="flex justify-between items-center mb-4">
                <!-- Title on the left, Subtitle on the right -->
                <div class="text-left">
                    <h2 class="text-2xl font-semibold">{{ section.title }}</h2>
                </div>
                <div class="ml-auto text-right">
                    <p class="text-gray-500">{{ section.sub_title }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Iterate over items in each section -->
                <div v-for="(item, idx) in section.items" :key="idx"
                    class="item bg-white p-4 shadow-md rounded-lg hover:shadow-lg hover:bg-gray-50"
                    :class="{ 'cursor-pointer': item.type === 'anchor' }">
                    <!-- Conditional rendering for anchor and non-anchor types -->
                    <component :is="item.type === 'anchor' ? 'a' : 'div'"
                        v-bind="item.type === 'anchor' ? { href: item.link, target: '_blank' } : {}"
                        class="block h-full">
                        <!-- Display image of the item -->
                        <img :src="item.image_website" alt="Item Image"
                            class="w-full h-48 object-cover mb-2 rounded-lg">
                        <!-- Display item name and details below the image -->
                        <h3 class="font-bold text-center">{{ item.name }}</h3>
                        <p class="text-sm text-center">{{ item.details }}</p>
                    </component>
                </div>
            </div>
        </div>
    </div>
</template>

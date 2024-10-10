<script setup lang="ts">

import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import dataList from "../data/blogActivity.js"
import PureInput from "@/Components/Pure/PureInput.vue"
import Popover from '@/Components/Popover.vue'
import { ref } from "vue"

const props = defineProps<{
    modelValue: any
}>()

const files = ref([
    {
        title: 'IMG_4985.HEIC',
        size: '3.9 MB',
        source: 'https://images.unsplash.com/photo-1582053433976-25c00369fc93?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=512&q=80',
    }
])
</script>

<template>
    <div class="container mx-auto py-8">
        <!-- Title section -->
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold">Range of Packaging and Raw Material</h1>
            <p class="text-gray-500">Create your own Brand of Incense with range of Fragrance Oils and Packaging</p>
        </div>

        <!-- Grid section -->
        <ul role="list" class="grid grid-cols-2 gap-x-6 gap-y-8 sm:grid-cols-3 lg:grid-cols-5">
            <li v-for="file in files" :key="file.source" class="relative text-center">
                <div
                    class="group aspect-h-7 aspect-w-10 block w-full overflow-hidden rounded-lg bg-gray-100 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 focus-within:ring-offset-gray-100">
                    <img :src="file.source" :alt="file.title"
                        class="pointer-events-none object-cover group-hover:opacity-75" />
                    <button type="button" class="absolute inset-0 focus:outline-none">
                        <span class="sr-only">View details for {{ file.title }}</span>
                    </button>
                </div>
                <p class="pointer-events-none mt-2 block truncate text-sm font-medium text-gray-900">{{ file.title }}
                </p>
            </li>
        </ul>
    </div>
</template>

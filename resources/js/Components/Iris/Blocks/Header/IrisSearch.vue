<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { trans } from 'laravel-vue-i18n'
import { router } from '@inertiajs/vue3'

import 'https://cdn.luigisbox.com/autocomplete.js'  // For autocomplete

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSearch } from '@far'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faSearch)

const luigiTrackerId = import.meta.env.VITE_LUIGI_TRACKER_ID
const defaultTrackerId = '179075-204259'

const searchValue = ref('')
const _inputRef = ref(null)


const LBInitAutocomplete = () => {
    AutoComplete({
        Layout: 'heromobile',
        TrackerId: luigiTrackerId,
        Locale: 'en',
        // Translations: {
        //     en: {
        //         types: {
        //             item: {
        //                 name: "Products",
        //                 heroName: "Top product"
        //             },
        //             query: {
        //                 name: "Searches"
        //             },
        //             category: {
        //                 name: "Categories"
        //             }
        //         }
        //     }
        // },
        Types: [
            {
                type: 'product',
                size: 7
            },
            {
                type: 'query'
            },
            {
                type: 'category'
            }
        ]
    }, '#inputLuigi')
}

onMounted(() => {
    LBInitAutocomplete()
})

const isUserMac = navigator.platform.includes('Mac')  // To check the user's Operating System

onMounted(() => {
    if (typeof window !== 'undefined') {
        document.addEventListener('keydown', (event) => {
            // Listen ctrl+k
            if( ( isUserMac ? event.metaKey : event.ctrlKey ) && event.key === 'k') {
                event.preventDefault()
                _inputRef.value?.focus()
            }
        })
    }
})

onUnmounted(() => {
    document.removeEventListener('keydown', () => false)
})

</script>

<template>
    <!-- Button: Search -->
    <div id="search"
        class="h-7 w-fit flex items-center justify-center gap-x-3 ring-1 ring-gray-300 rounded-md px-3 text-gray-500 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">
        <span class="sr-only">{{ trans("Search") }}</span>
        <FontAwesomeIcon aria-hidden="true" size="sm" icon="fa-regular fa-search" />

        <input
            ref="_inputRef"
            v-model="searchValue"
            @keydown.enter="() => router.visit(route('iris.search', { q: searchValue }))"
            id="inputLuigi"
            type="text"
            class="h-12 w-full border-0 bg-transparent px-0 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm"
            placeholder="Search...">

        <div class="whitespace-nowrap flex items-center justify-end text-gray-500/80 tracking-tight space-x-1">
            <span v-if="isUserMac" class="ring-1 ring-gray-400 bg-gray-100 px-2 leading-none text-xl rounded">⌘</span>
            <span v-else class="ring-1 ring-gray-400 bg-gray-100 px-2 py-0.5 text-xs rounded">Ctrl</span>
            <span class="ring-1 ring-gray-400 bg-gray-100 px-1.5 py-0.5 text-xs rounded">K</span>
        </div>
    </div>
</template>

<style>
@import 'https://cdn.luigisbox.com/autocomplete.css'; /* For autocomplete */

/* .lb-result__price{
    visibility: hidden;
} */

.luigi-ac-footer x {
    /* To hide copyright */
    visibility: hidden !important;
}
</style>
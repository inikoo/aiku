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

// const listFieldsRemoved = ['price', 'formatted_price', 'price_amount']
const listFieldsRemoved = undefined

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
        RemoveFields: listFieldsRemoved,
        type: ['item:5'],
        Types: [
            {
                name: "Item",
                type: "item",
                size: 7,
            },
            {
                name: "Query",
                type: "query",
            },
            {
                name: "Category",
                type: "category",
            },
        ],
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

<style lang="scss">
@import 'https://cdn.luigisbox.com/autocomplete.css'; /* For autocomplete */

$luigiColor1: #4b5058;
$luigiColor2: #957a65;
$luigiColor3: #e87928;

/* .lb-result__price{
    visibility: hidden;
} */

.luigi-ac-hero-color {
    background: $luigiColor1 !important;
    transition: background 0.05s !important;
}

.luigi-ac-hero-color:hover {
    background: color-mix(in srgb, $luigiColor1, 20% black) !important;
}

.luigi-ac-header {
    color: $luigiColor1 !important;
    font-size: 1.2rem !important;
    font-weight: bold !important;
}

.luigi-ac-highlight {
    background: color-mix(in srgb, $luigiColor3 90%, transparent) !important;
    color: #fff !important;
    padding-left: 1px !important;
    padding-right: 1px !important;
}

.luigi-ac-button {
    border-radius: 5px !important;
}

.luigi-ac-others {
    background: #eeeeee !important;
}

.luigi-ac-item {
    padding-top: 5px !important;
    padding-bottom: 5px !important;
}

.luigi-ac-item:hover {
    background: color-mix(in srgb, $luigiColor1 5%, transparent) !important;
    // background: darken($luigiColor2, 5%) !important;
    
}

.luigi-ac-heromobile .luigi-ac-first-main .luigi-ac-text {
    padding-top: 0.5rem !important
}

.luigi-ac-no-result {
    color: $luigiColor1 !important
}

// ====================================== Search result

.lb-search-text-color-primary {
    color: $luigiColor3 !important;
}

.lb-result__title {
    margin-bottom: 1px !important;
}

.lb-search .lb-result__description {
    text-align: justify;
    display: -webkit-box !important;
    -webkit-box-orient: vertical !important;
    -webkit-line-clamp: 3 !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    margin-bottom: 10px;
}

.lb-result__actions {
    display: flex !important;
    place-items: center !important;
    justify-content: space-between !important;
    row-gap: 5px !important;
}

.lb-result__prices {
    flex-grow: 1 !important;
    margin-bottom: 15px !important;
}

.lb-result__price {
    display: flex !important;
    place-content: center !important;
    text-align: center !important;
    color: $luigiColor3 !important;
}

.lb-result__action-buttons {
    flex-grow: 1 !important;
}


.lb-search .lb-result__action-item {
    width: 100% !important;
    margin: 0px !important
}
 
.lb-search-text-color-primary-clickable {
    color: $luigiColor2 !important;
}

.lb-search-bg-color-primary-clickable {
    background: transparent !important;
    color: $luigiColor2 !important;
    border: 1px solid $luigiColor2 !important;
    border-radius: 4px !important;
}

.lb-search-bg-color-primary-clickable:hover {
    background: color-mix(in srgb, $luigiColor2 40%, transparent) !important;
    
    // color: $luigiColor1 !important;
    // border: 1px solid $luigiColor1 !important;
    // border-radius: 4px !important;
}

.luigi-ac-footer {
    /* To hide copyright */
    visibility: hidden !important;
}
</style>
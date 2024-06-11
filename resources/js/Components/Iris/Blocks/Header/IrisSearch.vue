<script setup lang="ts">
import { inject, onMounted, ref, watch } from 'vue'
import { Dialog, DialogPanel, TransitionChild, TransitionRoot, } from '@headlessui/vue'
import { trans } from 'laravel-vue-i18n'
import { debounce } from 'lodash'
import { layoutStructure } from '@/Composables/useLayoutStructure'
import LoadingText from '@/Components/Utils/LoadingText.vue'

import 'https://cdn.luigisbox.com/search.js'  // For search
import 'https://cdn.luigisbox.com/autocomplete.js'  // For autocomplete


import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSearch } from '@far'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faSearch)

const luigiTrackerId = import.meta.env.VITE_LUIGI_TRACKER_ID
const defaultTrackerId = '179075-204259'

const isOpen = ref(false)
const searchValue = ref('')
const resultsSearch = ref()

const LBInitSearch = async () => {
    await Luigis.Search({
        TrackerId: luigiTrackerId,
        Locale: 'en',
        Theme: 'boo',
        Size: 10,
        UrlParamName: {
            QUERY: 'q',
        }
    }, '#inputLuigi', '#search-ui')
}

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

watch(isOpen, (newVal) => {
    if(newVal) {
        searchValue.value = route().params.q
        LBInitSearch()
        LBInitAutocomplete()
    }
})

const isUserMac = navigator.platform.includes('Mac')  // To check the user's Operating System

</script>

<template>
    <!-- Button: Search -->
    <button @click="isOpen = !isOpen" id="search"
        class="h-7 w-fit flex items-center justify-center gap-x-3 ring-1 ring-gray-300 rounded-md px-3 text-gray-500 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">
        <span class="sr-only">{{ trans("Search") }}</span>
        <FontAwesomeIcon aria-hidden="true" size="sm" icon="fa-regular fa-search" />
        <div class="mr-10">Search</div>
        <div class="whitespace-nowrap flex items-center justify-end text-gray-500/80 tracking-tight space-x-1">
            <span v-if="isUserMac" class="ring-1 ring-gray-400 bg-gray-100 px-2 leading-none text-xl rounded">⌘</span>
            <span v-else class="ring-1 ring-gray-400 bg-gray-100 px-2 py-0.5 text-xs rounded">Ctrl</span>
            <span class="ring-1 ring-gray-400 bg-gray-100 px-1.5 py-0.5 text-xs rounded">K</span>
        </div>
    </button>

    <TransitionRoot :show="isOpen" as="template" @after-leave="() => (searchValue = '', resultsSearch = [])" appear>
        <Dialog as="div" class="relative z-[21]" @close="() => isOpen = false">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-slate-700/25" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 pt-20 px-12">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 scale-95"
                    enter-to="opacity-100 scale-100" leave="ease-in duration-200" leave-from="opacity-100 scale-100"
                    leave-to="opacity-0 scale-95">
                    <DialogPanel class="isolate bg-white shadow-2xl pb-4 mx-auto max-w-3xl h-[calc(100vh-20vh)] transform rounded-xl ring-1 ring-black ring-opacity-5 transition-all">
                        <!-- Section: Search input -->
                        <div class="bg-white fixed z-20 top-0 w-full rounded-t-2xl border-b border-gray-300">
                            <FontAwesomeIcon class="absolute top-3.5 left-4 h-5 w-5 text-gray-400" aria-hidden="true" icon="fa-regular fa-search" size="lg" />
                            <input
                                v-model="searchValue"
                                @input="() => false"
                                @keydown.enzzzter="() => console.log('dsafdsaf')"
                                id="inputLuigi"
                                type="text"
                                class="h-12 w-full border-0 bg-transparent pl-11 pr-4 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm"
                                placeholder="Search...">
                        </div>

                        <div class="pt-10 px-7 h-full overflow-y-auto">
                            <div id="search-ui">
                                <div class="text-3xl font-bold w-full flex justify-center py-8">
                                    <LoadingText />
                                </div>
                            </div>
                        </div>

                        
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<style>
@import 'https://cdn.luigisbox.com/autocomplete.css'; /* For autocomplete */


.luigi-ac-footer {
    /* To hide copyright */
    visibility: hidden !important;
}
</style>
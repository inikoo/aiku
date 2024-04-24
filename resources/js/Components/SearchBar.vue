<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Mon, 06 Mar 2023 13:45:35 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import { ref } from 'vue'
import { Combobox, ComboboxOptions, ComboboxOption, Dialog, DialogPanel, TransitionChild, TransitionRoot, } from '@headlessui/vue'
import { Link } from '@inertiajs/vue3'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { trans } from 'laravel-vue-i18n'
import SearchResultGeneral from './Search/SearchResultGeneral.vue'
import { debounce } from 'lodash'

const props = defineProps<{
    isOpen: boolean
}>()

const emits = defineEmits<{
    (e: 'close', data: boolean): void
}>()

const isLoadingSearch = ref(false)
const searchValue = ref('');
const resultsSearch = ref()

// Method: parameter to string '&organisation=aw&fulfilment=idf'
const paramsToString = () => {
    return route().v().params ? '&' + Object.entries(route().v().params).map(([key, value]) => `${key}=${value}`).join('&') : ''
}

// Method: Fetch result
const fetchApi = debounce(async (query: string) => {
    if (query !== '') {
        resultsSearch.value = null
        isLoadingSearch.value = true
        await fetch(`${location.origin}/search?q=${query}&route_src=${route().current()}${paramsToString()}`)
            .then(response => {
                response.json().then((data: Object) => {
                    resultsSearch.value = data
                    isLoadingSearch.value = false
                })
            })
            .catch(err => console.log(err))
    }
}, 700)

</script>

<template>
    <TransitionRoot :show="isOpen" as="template" @after-leave="searchValue = ''" appear>
        <Dialog as="div" class="relative z-[21]" @close="emits('close', false)">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-slate-700 bg-opacity-35 transition-opacity" />
            </TransitionChild>
            
            <div class="fixed inset-0 z-10 overflow-y-auto pt-20 px-12">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 scale-95"
                    enter-to="opacity-100 scale-100" leave="ease-in duration-200" leave-from="opacity-100 scale-100"
                    leave-to="opacity-0 scale-95">
                    <DialogPanel class="mx-auto max-w-3xl transform divide-y divide-gray-100 overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black ring-opacity-5 transition-all">
                        <Combobox v-slot="{ activeOption }" @update:modelValue="() => console.log('ww')">
                            <!-- Section: Search input -->
                            <div class="relative">
                                <FontAwesomeIcon class="pointer-events-none absolute top-3.5 left-4 h-5 w-5 text-gray-400"
                                    aria-hidden="true" icon="fa-regular fa-search" size="lg" />
                                <input
                                    v-model="searchValue"
                                    @input="() => fetchApi(searchValue)"
                                    type="text"
                                    class="h-12 w-full border-0 bg-transparent pl-11 pr-4 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm"
                                    placeholder="Search..."
                                >
                            </div>

                            <!-- Result -->
                            <ComboboxOptions class="flex divide-x divide-gray-100" as="div" static hold>
                                <!-- Left: Result Panel -->
                                <div :class="['h-fit min-w-0 flex-auto scroll-py-4 overflow-y-auto px-6 py-4 transition-all duration-500 ease-in-out', {'sm:h-96': false}]">
                                    <div hold class="-mx-2 text-sm text-gray-600 space-y-2">
                                        <!-- Looping: Results -->
                                        <ComboboxOption v-if="resultsSearch?.data.length > 0" v-for="(item, itemIndex) in resultsSearch?.data" :key="itemIndex" :value="item" as="div" v-slot="{ active }">
                                            <Link v-if="item.model?.route?.name" :href="`${route(item.model?.route?.name, item.model?.route?.parameters)}`"
                                                class="group flex relative cursor-pointer select-none items-center rounded p-2 gap-x-2" :class="[active ? 'bg-gray-100 text-gray-600' : '']">
                                                <FontAwesomeIcon :icon='item.model.icon' class='' aria-hidden='true' />

                                                <div class="w-full">
                                                    <div v-if="item.model_type == 'CustomerUser'">
                                                        <span class="truncate">{{ item.model.contact_name }}</span>
                                                    </div>
                                                    <div v-else class="truncate font-semibold">
                                                        {{ item.model.name ?? item.model.email ?? item.model.phone ?? 'Unknown' }}
                                                    </div>
                                                </div>

                                                <FontAwesomeIcon icon="fa-regular fa-chevron-right" v-if="active" class="relative h-5 w-5 flex-none text-gray-400" aria-hidden="true" />
                                            </Link>
                                            
                                            <div v-else class="rounded p-2 bg-slate-50 hover:bg-slate-100 cursor-pointer">
                                                {{ item.model.slug || '' }}
                                            </div>
                                        </ComboboxOption>

                                        <!-- Loading: fetching -->
                                        <div v-else-if="isLoadingSearch" class="">
                                            <div class="space-y-2">
                                                <div class="w-full rounded-md flex pl-0.5 gap-x-1 overflow-hidden">
                                                    <div class="w-8 h-9 skeleton rounded-l-md" />
                                                    <div class="w-full skeleton"/>
                                                </div>
                                                <div class="w-full rounded-md flex pl-0.5 gap-x-1 overflow-hidden">
                                                    <div class="w-8 h-9 skeleton rounded-l-md" />
                                                    <div class="w-full skeleton"/>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Initial state or no result -->
                                        <div v-else class="p-2">
                                            {{ trans('Nothing to show') }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Right: Detail Panel -->
                                <div class="hidden h-96 w-1/2 flex-none flex-col divide-y divide-gray-100 overflow-y-auto sm:flex">
                                    <!-- Loading: fetching -->
                                    <div v-if="isLoadingSearch">
                                        <div class="flex-none p-6 text-center">
                                            <div class="mx-auto h-16 w-16 rounded-full skeleton" />
                                            <div class="mt-3 skeleton w-1/2 mx-auto h-5" />
                                        </div>
                                        <div class="flex flex-auto flex-col justify-between gap-y-4 p-6">
                                            <div v-for=" of 3" class="flex gap-x-2 h-7 rounded overflow-hidden">
                                                <div class="skeleton w-20" />
                                                <div class="skeleton w-full" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hover the result -->
                                    <div v-else-if="activeOption" class="flex flex-auto flex-col justify-between p-6">
                                        <SearchResultGeneral :activeOption="activeOption" />
                                    </div>
                                </div>
                            </ComboboxOptions>
                        </Combobox>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

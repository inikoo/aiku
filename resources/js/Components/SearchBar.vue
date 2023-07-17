<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Mon, 06 Mar 2023 13:45:35 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import { computed, ref } from 'vue'
import {
    Combobox,
    ComboboxOptions,
    ComboboxOption,
    Dialog,
    DialogPanel,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue'
import { usePage } from '@inertiajs/vue3'
import {router} from "@inertiajs/vue3";
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

const searchResults = computed(() => usePage().props.searchResults)

const open = ref(true)
const query = ref('')

const searchInput = ref('');

let timeoutId;

function handleSearchInput() {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
        console.log(searchInput.value);
        router.get(
            route('search.run', {
                _query: {
                    q: searchInput.value
                }
            })
        );

    }, 200);
}

function handleKeyDown() {
    clearTimeout(timeoutId);
}

</script>

<template>
    <TransitionRoot :show="open" as="template" @after-leave="query = ''" appear>
        <Dialog as="div" class="relative z-[19]" @close="open = false">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-25 transition-opacity" />
            </TransitionChild>
            <div class="fixed inset-0 z-10 overflow-y-auto pt-20 px-12">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="ease-in duration-200" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                    <DialogPanel class="mx-auto max-w-3xl transform divide-y divide-gray-100 overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black ring-opacity-5 transition-all">
                        <Combobox v-slot="{ activeOption }" @update:modelValue="onSelect">
                            <div class="relative">
                                <FontAwesomeIcon class="pointer-events-none absolute top-3.5 left-4 h-5 w-5 text-gray-400" aria-hidden="true" icon="fa-regular fa-search" size="lg"/>
                                <input type="text" v-model="searchInput" @input="handleSearchInput" @keydown="handleKeyDown"
                                class="h-12 w-full border-0 bg-transparent pl-11 pr-4 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm" placeholder="Search..." @change="query = $event.target.value">
                            </div>
                            <ComboboxOptions  class="flex divide-x divide-gray-100" as="div" static hold>
                                <div :class="['max-h-96 min-w-0 flex-auto scroll-py-4 overflow-y-auto px-6 py-4', activeOption && 'sm:h-96']">
                                    <div hold class="-mx-2 text-sm text-gray-700">
                                        <ComboboxOption v-for="item in searchResults.data" :key="item.id" :value="item" as="template" v-slot="{ active }">
                                            <div :class="['group flex cursor-default select-none items-center rounded-md p-2', active && 'bg-gray-100 text-gray-900']">
                                                <img :src="item.imageUrl" alt="" class="h-6 w-6 flex-none rounded-full" />
                                                <span class="ml-3 flex-auto truncate">{{ item.name }}</span>
                                                <FontAwesomeIcon icon="fa-regular fa-chevron-right" v-if="active" class="ml-3 h-5 w-5 flex-none text-gray-400" aria-hidden="true" />
                                            </div>
                                        </ComboboxOption>
                                    </div>
                                </div>
                                <div v-if="activeOption" class="hidden h-96 w-1/2 flex-none flex-col divide-y divide-gray-100 overflow-y-auto sm:flex">
                                    <div class="flex-none p-6 text-center">
                                        <img :src="activeOption.imageUrl" alt="" class="mx-auto h-16 w-16 rounded-full" />
                                        <h2 class="mt-3 font-semibold text-gray-900">
                                            {{ activeOption.name }}
                                        </h2>
                                        <p class="text-sm leading-6 text-gray-500">{{ activeOption.role }}</p>
                                    </div>
                                    <div class="flex flex-auto flex-col justify-between p-6">
                                        <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm text-gray-700">
                                            <dt class="col-end-1 font-semibold text-gray-900">Phone</dt>
                                            <dd>{{ activeOption.phone }}</dd>
                                            <dt class="col-end-1 font-semibold text-gray-900">URL</dt>
                                            <dd class="truncate">
                                                <a :href="activeOption.url" class="text-indigo-600 underline">
                                                    {{ activeOption.url }}
                                                </a>
                                            </dd>
                                            <dt class="col-end-1 font-semibold text-gray-900">Email</dt>
                                            <dd class="truncate">
                                                <a :href="`mailto:${activeOption.email}`" class="text-indigo-600 underline">
                                                    {{ activeOption.email }}
                                                </a>
                                            </dd>
                                        </dl>
                                        <button type="button" class="mt-6 w-full rounded-md bg-indigo-600 py-2 px-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Send message</button>
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

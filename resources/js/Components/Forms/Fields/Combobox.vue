<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import {
    Combobox,
    ComboboxInput,
    ComboboxButton,
    ComboboxOptions,
    ComboboxOption,
    TransitionRoot,
} from '@headlessui/vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faChevronDown, faCheck } from "@/../private/pro-solid-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faChevronDown, faCheck)

defineEmits(['update:modelValue'])
const props = defineProps({
    modelValue: Object,
    options: {
        type: Array,
        default: () => [],
    },
    loadOptions: Function,
})

const options = ref(props.options)

let query = ref('')
watch(query, (q) => {
    if(props.loadOptions) {
        props.loadOptions(q, (results) => {
            options.value = results
        })
    }
})

let filteredOptions = computed(() =>
    query.value === ''
        ? options.value
        : options.value.filter((person) =>
            person.name
                .toLowerCase()
                .replace(/\s+/g, '')
                .includes(query.value.toLowerCase().replace(/\s+/g, ''))
        )
)
</script>
  
<template>
    <Combobox by="value" :model-value="props.modelValue" @update:model-value="value => $emit('update:modelValue', value)">
        <div class="relative mt-1">
            <div
                class="relative w-full cursor-default overflow-hidden rounded-lg bg-white text-left border border-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-700 focus-visible:ring-opacity-75 focus-visible:ring-offset-2 focus-visible:ring-offset-teal-300 sm:text-sm">
                <ComboboxInput class="w-full border-none py-2 pl-3 pr-10 text-sm leading-5 text-gray-900 focus:ring-0"
                    :displayValue="(person) => person.name" @change="query = $event.target.value" />
                <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-2">
                    <FontAwesomeIcon icon="fas fa-chevron-down" class="h-4 w-4 text-gray-400" aria-hidden="true" />
                </ComboboxButton>
            </div>
            <TransitionRoot leave="transition ease-in duration-100" leaveFrom="opacity-100" leaveTo="opacity-0"
                @after-leave="query = ''">
                <ComboboxOptions
                    class="absolute mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                    <div v-if="filteredOptions.length === 0 && query !== ''"
                        class="relative cursor-default select-none py-2 px-4 text-gray-700">
                        Nothing found.
                    </div>

                    <ComboboxOption v-for="person in filteredOptions" as="template" :key="person.id" :value="person"
                        v-slot="{ selected, active }">
                        <li class="relative cursor-default select-none py-2 pl-10 pr-4" :class="{
                            'bg-teal-600 text-white': active,
                            'text-gray-900': !active,
                        }">
                            <span class="block truncate" :class="{ 'font-medium': selected, 'font-normal': !selected }">
                                {{ person.name }}
                            </span>
                            <span v-if="selected" class="absolute inset-y-0 left-0 flex items-center pl-3"
                                :class="{ 'text-white': active, 'text-teal-600': !active }">
                                <FontAwesomeIcon icon="fas fa-check" class="h-4 w-4 text-indigo-600" aria-hidden="true" />
                            </span>
                        </li>
                    </ComboboxOption>
                </ComboboxOptions>
            </TransitionRoot>
        </div>
    </Combobox> 
</template>
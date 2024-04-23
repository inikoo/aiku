<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { trans } from 'laravel-vue-i18n'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faFilter, faTimesCircle } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faFilter, faTimesCircle)

const emit = defineEmits(['resetSearch'])

defineProps({
    label: {
        type: String,
        default: "Search on table...",
        required: false,
    },

    value: {
        type: String,
        default: "",
        required: false,
    },

    onChange: {
        type: Function,
        required: true,
    },
});

onMounted(() => {
    if (typeof window !== 'undefined') {
        document.addEventListener('keydown', (e) => e.key == '/' ? document.getElementById("tableinput")?.focus() : false)
        document.addEventListener('keydown', (e) => e.key == 'Escape' ? document.getElementById("tableinput")?.blur() : false)
    }
})

onUnmounted(() => {
    document.removeEventListener('keydown', () => false)
})

const isUserMac = navigator.platform.includes('Mac')  // To check the user's Operating System

</script>

<template>
    <div class="group relative" :title="trans('Search on table')">
        <input
            id="tableinput"
            placeholder="Type something.."
            :value="value" type="text" name="global" @input="onChange($event.target.value)"
            class="appearance-none bg-white block pl-[103px] pr-0.5 w-0 text-sm rounded-md shadow-sm transition-[width] duration-150 ease-in-out placeholder:text-gray-400 placeholder:italic ring-0 ring-transparent focus:ring-0 focus:ring-transparent cursor-pointer"
            :class="[value ? 'border-2 border-gray-500 focus:border-gray-500 text-gray-500 w-full' : 'group-focus-within:w-full group-focus-within:pr-9 border-0 border-r border-gray-300 focus:border-gray-300']"
        >
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none space-x-1.5">
            <FontAwesomeIcon icon="fal fa-filter" class="h-4 w-4" aria-hidden="true"
                :class="[value ? 'text-gray-500' : 'text-gray-400']" 
            />
            <span v-if="isUserMac" class="ring-1 ring-gray-400 bg-gray-100 px-2 leading-none py-0.5 text-base rounded">⌥</span>
            <span v-else class="ring-1 ring-gray-400 bg-gray-100 px-2 py-1 text-xs rounded leading-none">Alt</span>
            <span class="ring-1 ring-gray-400 bg-gray-100 px-2 py-0.5 text-xs rounded">/</span>
        </div>

        <div v-if="value" tabindex="0" class="hidden group-focus-within:flex absolute inset-y-0 right-2  items-center pointer-events-auto cursor-pointer" @click="emit('resetSearch', true)">
            <FontAwesomeIcon icon="fal fa-times-circle" class="h-4 w-4 text-gray-400" aria-hidden="true" />
        </div>
    </div>
</template>

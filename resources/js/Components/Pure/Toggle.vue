<script setup lang="ts">
import { Switch } from '@headlessui/vue'

const props = defineProps<{
    modelValue: boolean
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
}>()

</script>


<template>
    <div class="transition-all duration-1000 ease-in-out">
        <Switch
            :modelValue="modelValue"
            @update:modelValue="(val) => emit('update:modelValue', val)"
            :class="modelValue ? 'bg-slate-600' : 'bg-slate-300'"
            class="pr-1 relative inline-flex h-6 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75"
        >
            <!-- <span class="sr-only">Use setting</span> -->
            <span aria-hidden="true" :class="modelValue ? 'translate-x-6' : 'translate-x-0'"
                class="pointer-events-none inline-block h-full w-1/2 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out" />
        </Switch>
    </div>
</template>
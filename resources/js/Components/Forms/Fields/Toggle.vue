<template>
    <div class="">
        <Switch v-model="enabled" :class="enabled ? 'bg-indigo-500' : 'bg-indigo-100'"
            class="pr-1 relative inline-flex h-8 w-16 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
            <span class="sr-only">Use setting</span>
            <span aria-hidden="true" :class="enabled ? 'translate-x-8' : 'translate-x-0'"
                class="pointer-events-none inline-block h-full w-1/2 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out" />
        </Switch>
    </div>
</template>
  
<script setup>
import { ref } from 'vue'
import { Switch } from '@headlessui/vue'

const enabled = ref(false)
</script>
  
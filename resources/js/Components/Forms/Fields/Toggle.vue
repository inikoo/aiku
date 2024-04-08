<script setup>
import { ref } from 'vue'
import { Switch } from '@headlessui/vue'

const props = defineProps(['form', 'fieldName', 'fieldData'])

const enabled = ref(false)
</script>
<template>
    <div>
        <Switch v-model="form[fieldName]" :class="form[fieldName] ? 'bg-indigo-500' : 'bg-indigo-100'"
            class="pr-1 relative inline-flex h-6 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
            <span class="sr-only">Use setting</span>
            <span aria-hidden="true" :class="form[fieldName] ? 'translate-x-6' : 'translate-x-0'"
                class="pointer-events-none inline-block h-full w-1/2 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out" />
        </Switch>
    </div>
</template>
  

  
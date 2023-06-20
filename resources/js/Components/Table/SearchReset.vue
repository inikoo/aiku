<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faTimes } from "@/../private/pro-light-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faTimes)

const emit = defineEmits(['resetSearch'])

</script>

<template>
  <button
    ref="button"
    type="button"
    dusk="reset-table"
    class="w-full bg-white border rounded-md shadow-sm px-4 py-2 inline-flex justify-center text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 border-gray-300"
    aria-haspopup="true"
    @click="emit('resetSearch', true)"
  >
    <FontAwesomeIcon icon="fal fa-times"  class="h-5 w-5 mr-2 text-gray-400" aria-hidden="true" />
    <span class="text-gray-500">Reset</span>
  </button>
</template>



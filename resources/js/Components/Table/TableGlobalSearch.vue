<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSearch } from '@far'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faSearch)

defineProps({
    label: {
        type: String,
        default: "Search...",
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
</script>

<template>
  <div class="relative">
    <input
      class="block w-full pl-9 text-sm rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300"
      :placeholder="label"
      :value="value"
      type="text"
      name="global"
      @input="onChange($event.target.value)"
    >
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
      <FontAwesomeIcon icon="far fa-search" class="h-4 w-4 text-gray-400" aria-hidden="true" />
    </div>
  </div>
</template>

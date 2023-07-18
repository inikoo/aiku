<script setup>
import { ref } from 'vue'

const props = defineProps({
  size: {
    type: String,
    default: '12'
  },
  changesize: {
    type: Function,
    required: true
  }
})

const sizeValue = ref(parseInt(props.size.match(/\d+/)[0], 10))

const setValue = () => {
  props.changesize(sizeValue.value)
}
</script>

<template>
  <div>
    <div class="flex rounded-md w-16 shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
      <input
        v-model="sizeValue"
        @input="setValue"
        type="number"
        class="block flex-1 w-16 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm"
        placeholder="Size"
      />
    </div>
  </div>
</template>

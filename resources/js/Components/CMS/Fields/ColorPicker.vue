<script setup>
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import { ColorPicker } from 'vue-color-kit'
import 'vue-color-kit/dist/vue-color-kit.css'
import { ref } from 'vue'
const props = defineProps({
  color: {
    type: String,
    default: 'rgba(0, 0, 0, 0)'
  },
  changeColor: {
    type: Function,
  },
});


const changeColor = (set) => {
  const { r, g, b, a } = set.rgba
  props.changeColor(`rgba(${r}, ${g}, ${b}, ${a})`)
}
</script>


<template>
  <div>
    <Popover v-slot="{ open }" class="relative">
      <PopoverButton>
        <div
          class="h-8 w-8 rounded-full border border-black bg-color-red border-opacity-10 flex items-center justify-center"
          :style="`background-color: ${color}`" />
      </PopoverButton>

      <transition enter-active-class="transition duration-200 ease-out" enter-from-class="translate-y-1 opacity-0"
        enter-to-class="translate-y-0 opacity-100" leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-1 opacity-0">
        <PopoverPanel class="relative left-1/2 z-10 mt-3  -translate-x-1/2 transform px-4 sm:px-0 ">
          <div class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
            <div class="relative  bg-white p-2.5">
              <ColorPicker style="width: 220px;" theme="light" :color="color" :sucker-hide="true" @changeColor="changeColor" />
            </div>
          </div>
        </PopoverPanel>
      </transition>
    </Popover>
  </div>
</template>
  
  
  
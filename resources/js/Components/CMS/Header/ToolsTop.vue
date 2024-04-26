<script setup lang="ts">
import { faHandPointer, faHandRock, faPlus } from '@fas';
import { library } from '@fortawesome/fontawesome-svg-core';
import {  ref } from 'vue'

library.add(faHandPointer, faHandRock, faPlus)
const props = defineProps({
  theme: String,
});


const theme = ref(props.theme)

const emits = defineEmits();

const themeOption = ["simpleSticky","simple"]

const Bluprint = [
  {
    name: 'theme',
    position: 'left',
    optionsData: {
      options: themeOption
    }
  },
]


</script>

<template>
  <div class="h-[40px] bg-white p-[5px] w-full flex">
    <div class="w-1/2 flex justify-start items-center">
  </div>

    <div class="w-1/2 flex justify-end items-center">
      <div v-for="item in Bluprint.filter((item) => item.position === 'left')" :key="item.name">
        <select v-model="theme" @change="emits('changeTheme',theme)" v-if="item.name === 'theme'"
          class="px-2 py-1 rounded-md border-gray-300 border w-[200px]">
          <option v-for="option in item.optionsData.options" :key="option" :value="option">{{ option }}</option>
        </select>
      </div>
    </div>
  </div>
</template>


<style></style>




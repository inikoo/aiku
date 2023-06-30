<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps({
  data: Object,
  checkboxValues: Array,
  toggleCheckbox: Function
});

const checkboxValues = ref(props.checkboxValues);

function toggleCheckbox(value) {
  if (checkboxValues.value.includes(value)) {
    checkboxValues.value = checkboxValues.value.filter(item => item !== value);
  } else {
    checkboxValues.value.push(value);
  }
  props.toggleCheckbox(value);
}
</script>

<template>
  <div class="rounded overflow-hidden shadow-lg border border-gray-300">
    <div class="CardHead">
      <div class="flex">
        <div class="font-bold text-sm">{{ data.title }}</div>
        <div class="flex-grow"></div>
        <label class="inline-flex items-center">
          <input type="checkbox" id="mike1" value="Mike" class="form-checkbox h-4 w-4 text-indigo-600"
            style="margin-right: 10px;">
        </label>
      </div>
      <hr class="my-2">
    </div>
    <div class="grid grid-cols-2 gap-4 CardContent">
      <div v-for="(per, key) in data.permissions" :key="key">
        <span
          class="inline-flex items-center bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-500 w-full cursor-pointer"
          @click="toggleCheckbox(per.name)">
          <input type="checkbox" :id="per.name" :value="per.name" v-model="checkboxValues"
            class="form-checkbox h-4 w-4 text-indigo-600">
          <label :for="per.name" class="ml-2">{{ per.name }}</label>
        </span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.CardContent {
  padding: 0px 10px 10px 10px;
}

.CardHead {
  padding-top: 10px;
  padding-bottom: 2px;
  padding-left: 10px;
  padding-right: 10px;
}
</style>

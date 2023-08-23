<script setup lang="ts">
import { ref, onMounted } from "vue"
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '../../../../private/pro-solid-svg-icons';
import { fab } from "@fortawesome/free-brands-svg-icons"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
library.add( fab, fas)
const props = defineProps<{
	data: Object
	save: Function
  modelLink : String
  modelLabel : String
}>()

const labelValue = ref(props.data[props.modelLabel])
const linkValue = ref(props.data[props.modelLink])

const handleBlur = (type) => {
   const set = {...props.data, [props.modelLabel] : labelValue.value, [props.modelLink] :  linkValue.value}
   props.save(set,type)
  }


</script>

<template>
<div class="mt-5 flex gap-2">
  <div class="flex-1" style="width:87%;">
    <div class="flex shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
      <span class="flex select-none items-center pl-3 text-gray-500 sm:text-sm"><font-awesome-icon :icon="['fas', 'tag']" /></span>
      <input style="width:86%;" @blur="handleBlur('edit')" v-model="labelValue" type="text" class="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6" placeholder="label" />
    </div>
    <div class="flex shadow-sm ring-1 mt-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
      <span class="flex select-none items-center pl-3 text-gray-500 sm:text-sm"><font-awesome-icon :icon="['fas', 'link']" /></span>
      <input style="width:86%;" @blur="handleBlur('edit')" v-model="linkValue" type="text" class="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6" placeholder="link" />
    </div>
  </div>
  <div class="flex justify-center align-middle">
    <button @click.prevent="handleBlur('delete')" class="rounded-md cursor-pointer border ring-gray-300 px-3 py-2 text-sm font-semibold text-black shadow-sm">
  <font-awesome-icon :icon="['fas', 'trash']" />
</button>

  </div>
</div>

</template>

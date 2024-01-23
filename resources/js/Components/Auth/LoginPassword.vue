<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Tue, 08 Nov 2022 22:50:49 Malaysia Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup>
import {onMounted, ref} from 'vue';
import {library} from '@fortawesome/fontawesome-svg-core';
import {faEye, faEyeSlash} from '@far';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

library.add(faEye, faEyeSlash);

defineProps(['modelValue', 'id', 'name', 'showPassword']);

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});
</script>

<template>
    <div class="relative flex items-stretch flex-grow focus-within:z-10">

        <input :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" ref="input" :id="id" :name="name" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" required=""
               class="focus:ring-indigo-500 focus:border-indigo-500 block w-full rounded-none rounded-l-md border-gray-300"/>
    </div>
    <button @click="showPassword=!showPassword" type="button"
            class="-ml-px relative inline-flex items-center  px-4 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
        <font-awesome-icon v-show=showPassword aria-hidden="true" class="h-5 w-5 text-gray-400" icon="fa-regular fa-eye"/>
        <font-awesome-icon v-show=!showPassword aria-hidden="true" class="h-5 w-5 text-gray-400" icon="fa-regular fa-eye-slash"/>
    </button>
</template>

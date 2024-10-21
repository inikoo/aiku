<script setup lang="ts">
import { ref, defineEmits, watch } from 'vue'
import { trans } from 'laravel-vue-i18n'

const props = defineProps<{
    modelValue: Object
}>()
</script>
<template>
    <div class="border-b pb-3 border-gray-300 mb-5 px-2 flex items-center">
        <div class="my-2 text-gray-500 font-semibold text-lg">{{ trans('Visibility') }}</div>
        <div class="border-l-2 border-gray-300 h-6 mx-4"></div> <!-- Vertical Line -->
        <div class="flex space-x-8">
            <div class="flex items-center">
                <input type="checkbox" id="loggedIn" :checked="modelValue.in" @change="modelValue.in = !modelValue.in"
                    class="form-checkbox h-5 w-5 text-indigo-500 border-gray-300 rounded focus:ring-indigo-500" />
                <label for="loggedIn" class="ml-2 text-gray-700 cursor-pointer hover:text-indigo-600 text-xs">{{
                    trans('Logged In') }}</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" id="loggedOut" :checked="modelValue.out"
                    @change="modelValue.out = !modelValue.out"
                    class="form-checkbox h-5 w-5 text-indigo-500 border-gray-300 rounded focus:ring-indigo-500" />
                <label for="loggedOut" class="ml-2 text-gray-700 cursor-pointer hover:text-indigo-600 text-xs">{{
                    trans('Logged Out') }}</label>
            </div>
        </div>
    </div>
</template>


<style lang="scss" scoped></style>

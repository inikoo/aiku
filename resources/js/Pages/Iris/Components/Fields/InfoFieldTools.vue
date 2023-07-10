<script setup lang="ts">
import { ref, onMounted } from "vue"
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@/../private/pro-solid-svg-icons';
import { fal } from '@/../private/pro-light-svg-icons';
import { far } from '@/../private/pro-regular-svg-icons';
import { fad } from '@/../private/pro-duotone-svg-icons';
import { fab } from "@fortawesome/free-brands-svg-icons"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import IconPicker from "../Fields/IconPicker/IconPicker.vue"
library.add(fab, fas, fal, far, fad)
const props = defineProps<{
    data: Object
    save: Function
}>()

const labelValue = ref(props.data.value)

const handleBlur = (type) => {
    const set = { ...props.data, value: labelValue.value }
    props.save(set, type)
}

const setIcon = (value) => {
    const set = { ...props.data, value: labelValue.value, icon : value.value.value }
    props.save(set, 'edit')
}

</script>

<template>
    <div class="mt-5 flex gap-2">
        <div class="flex-1" style="width:87%;">
            <div
                class="flex shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                <span class="flex select-none p-2.5  border-gray-500 items-center pl-3 text-gray-500 sm:text-sm">
                    <IconPicker :modelValue="data.icon" :data="data" :save="setIcon" />
                </span>
                <input @blur="handleBlur('edit')" v-model="labelValue" type="text"
                    class="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                    placeholder="label" />
            </div>
        </div>
        <div class="flex justify-center align-middle">
            <button @click.prevent="handleBlur('delete')"
                class="rounded-md cursor-pointer border ring-gray-300 px-3 py-2 text-sm font-semibold text-black shadow-sm">
                <font-awesome-icon :icon="['fas', 'trash']" />
            </button>

        </div>
    </div>
</template>

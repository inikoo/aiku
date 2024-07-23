<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'

const props = defineProps<{
    modelValue: string,
}>()
const customDiv = ref<HTMLElement | null>(null);
const edit = ref(false)
const inputRef = ref<HTMLInputElement | null>(null);
const emits = defineEmits<{
    (e: 'update:modelValue', value: string): void
}>()

const handleClickOutside = (event: MouseEvent) => {
    if (customDiv.value && !customDiv.value.contains(event.target as Node)) {
        edit.value = false;
    }
};

const updateValue = (value: string) => {
    emits('update:modelValue', value);
};

const changeEdit = () => {
    edit.value = true;
    nextTick(() => {
        if (inputRef.value) {
            inputRef.value.focus();
        }
    });
}

/* onMounted(() => {
    document.addEventListener('click', handleClickOutside);
}); */

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});


</script>

<template>
    <div ref="customDiv" class="min-h-5">
        <div v-if="!edit" @click="changeEdit" class="cursor-pointer"
            :class="modelValue == '' ? 'text-gray-400 border-2 border-dashed font-light text-xs px-2 py-1' : ''">{{ modelValue == '' ? 'area value'
                : modelValue }}</div>
        <input ref="inputRef" class="w-full" @click.stop v-if="edit" :value="modelValue"
            @input="updateValue($event.target.value)" @blur="edit = false" />
    </div>
</template>

<style scoped>
input {
    padding-top: 0rem;
    padding-right: 0rem;
    padding-bottom: 0rem;
    padding-left: 0rem;
}
</style>

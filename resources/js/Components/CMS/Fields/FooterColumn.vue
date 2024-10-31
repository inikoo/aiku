<script setup lang="ts">
import { ref } from 'vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'
import { cloneDeep } from 'lodash'


const props = defineProps<{
    modelValue: any,
}>();

const emits = defineEmits<{
    (e: 'update:modelValue', value: {}): void
}>();

</script>

<template>
   fdgdsfgfdg
</template>

<style scoped></style>

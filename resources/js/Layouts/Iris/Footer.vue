<script setup lang='ts'>
import { getComponent, getDescriptor } from '@/Components/Websites/Footer/Content'


const props = defineProps<{
    data: {
        key: string,
        data: object,
        bluprint: object
    }
    colorThemed: object
}>()

</script>

<template>
    <div>
        <component :is="getComponent(data.key)"  v-model="data.data"
        :keyTemplate="data.key" :previewMode="true" :colorThemed="colorThemed" />
    </div>

</template>
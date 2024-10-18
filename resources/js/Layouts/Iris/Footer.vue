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

console.log('footer data', props)

</script>

<template>
    <div v-if="data?.data?.footer">
        <component :is="getComponent(data.code)" v-model="data.data.footer" :keyTemplate="data.code" :previewMode="true"
            :colorThemed="colorThemed" />
    </div>

</template>
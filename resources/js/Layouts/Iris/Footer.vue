<script setup lang='ts'>
import { getComponent } from '@/Composables/getIrisComponents'

import { Root } from '@/types/Website/Website/footer1'


const props = defineProps<{
    data: Root
    colorThemed: object
}>()


</script>

<template>
    <div>
        <component 
            :is="getComponent(data.code)" 
            v-model="data.data.fieldValue" 
            :keyTemplate="data.code" 
            :previewMode="true"
            :colorThemed="colorThemed" 
        />
    </div>
</template>
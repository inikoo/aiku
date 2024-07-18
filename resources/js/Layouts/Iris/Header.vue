<script setup lang='ts'>
import { getRenderComponent } from "@/Components/Websites/Header/Content"


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
        <component :is="getRenderComponent(data.key)" :loginMode="true"  :data="data.data" :colorThemed="colorThemed" />
    </div>

</template>
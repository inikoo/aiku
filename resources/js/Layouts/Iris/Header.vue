<script setup lang='ts'>
import { getRenderComponent } from "@/Components/Websites/Header/Content"


const props = defineProps<{
    data: {
        key: string,
        data: object,
        bluprint: object
        loginRoute?: routeType
    }
  
    colorThemed: object
}>()

console.log('sssa',props)


</script>

<template>
    <div>
        <component :is="getRenderComponent(data.key)" :loginMode="false" :loginRoute="data.loginRoute" :data="data.data" :colorThemed="colorThemed" />
    </div>

</template>
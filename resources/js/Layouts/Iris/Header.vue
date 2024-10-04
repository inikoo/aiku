<script setup lang='ts'>
import { getRenderComponent } from "@/Components/Websites/Header/Content"
import NavigationMenu from '@/Layouts/Iris/NavigationMenu.vue'
import { routeType } from "@/types/route"


const props = defineProps<{
    data: {
        key: string,
        data: object,
        bluprint: object
        loginRoute?: routeType
    }
    menu : {
        key: string,
        data: object,
        bluprint: object
    }
    colorThemed: object
}>()

</script>

<template>
    <div>
        <component 
            :is="getRenderComponent(data?.data?.key)" 
            :loginMode="false" 
            :loginRoute="data?.data?.loginRoute" 
            :data="data.data" 
            :colorThemed="colorThemed"
            :menu="menu"
        />
        
        <NavigationMenu :data="menu" :colorThemed="colorThemed" class="hidden md:block" />
    </div>
</template>
<script setup lang='ts'>
import { getComponent as getComponentsHeader } from '@/Components/Websites/Header/Content'
import NavigationMenu from './MenuRender.vue'
import { routeType } from "@/types/route"
import IrisLoginInformation from '@/Layouts/Iris/IrisLoginInformation.vue'
import { getTopbarComponent } from '@/Components/Websites/Topbar/TopbarList'

const props = defineProps<{
    data: {
        key: string,
        data: object,
        bluprint: object
        loginRoute?: routeType
    }
    menu: {
        key: string,
        data: object,
        bluprint: object
    }
    colorThemed: {
        color: string[]
    }
}>()

console.log('p-header',props)

</script>

<template>
    <div>

        <!-- <IrisLoginInformation /> -->
        <!-- {{ colorThemed }} -->

        <component :is="getTopbarComponent('topbar1')" v-model="data.topBar.data" :loginMode="true"
            :previewMode="true" :uploadImageRoute="null" :colorThemed="colorThemed" />


        <component :is="getComponentsHeader(data?.header?.data?.key)" :loginMode="true" :previewMode="true"
            v-model="data.header.data" :uploadImageRoute="null" :colorThemed="colorThemed" />

        <NavigationMenu v-if="menu" :data="menu" :colorThemed="colorThemed" class="hidden md:block" />
    </div>
</template>
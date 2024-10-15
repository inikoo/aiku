<script setup lang='ts'>
import { getComponent as getComponentsHeader} from '@/Components/Websites/Header/Content'
import NavigationMenu from './MenuRender.vue'
import { routeType } from "@/types/route"
import IrisLoginInformation from '@/Layouts/Iris/IrisLoginInformation.vue'


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
    colorThemed : {
        color: string[]
    }
}>()

</script>

<template>
    <div>

        <!-- <IrisLoginInformation /> -->
        <!-- {{ colorThemed }} -->


        <component
            :is="getComponentsHeader(data?.header?.key)"
            :loginMode="true"
            :previewMode="true"
            v-model="data.header"
            :uploadImageRoute="data.uploadImageRoute"
            :colorThemed="colorThemed"
        />

        <NavigationMenu v-if="menu"  :data="menu" :colorThemed="colorThemed" class="hidden md:block" />
    </div>
</template>
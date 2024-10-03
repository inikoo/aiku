<script setup lang='ts'>
import { getComponent } from "@/Components/Websites/Menu/Content"

import { faBox, faDollarSign, faHome as fasHome, faUserPlus, faSignIn } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faHome, faInfoCircle, faPhone, faFile, faChevronDown } from '@fal'


library.add(faBox, faDollarSign, fasHome, faUserPlus, faSignIn, faHome, faInfoCircle, faPhone, faFile, faChevronDown)

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
       <component :is="getComponent(data.menu.code)" :navigations="data.menu.data.navigation" :colorThemed="colorThemed"/>
</template>
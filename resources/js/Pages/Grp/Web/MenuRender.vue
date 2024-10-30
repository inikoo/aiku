<script setup lang='ts'>
import { getComponent } from "@/Components/CMS/Website/Menus/Content"

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

console.log('menu2',props)
</script>


<template>
       <component 
            v-if="data?.data?.fieldValue" 
            :is="getComponent(data.code)" 
            :navigations="data.data.fieldValue.navigation" 
            :colorThemed="colorThemed"
        />
</template>
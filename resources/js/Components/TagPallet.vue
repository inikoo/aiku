<script setup lang='ts'>
import Tag from '@/Components/Tag.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSeedling, faShare, faSpellCheck, faCheck, faCheckDouble, faSortSizeUp } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faSeedling, faShare, faSpellCheck, faCheck, faCheckDouble, faSortSizeUp)
    
defineProps<{
    stateIcon: {
        tooltip: string
        icon: string | string[]
        color: string
    }
}>()


const getClass = (colorName: string) => {
    return `bg-${colorName}-100 border border-${colorName}-200 text-${colorName}-500`
}

</script>

<template>
    <Tag :label="stateIcon.tooltip" :class="getClass(stateIcon.color)">
        <template #label>
            <FontAwesomeIcon :icon='stateIcon.icon' class='' fixed-width aria-hidden='true' />
            <span class="whitespace-nowrap capitalize">{{ stateIcon.tooltip }}</span>
        </template>
    </Tag>
</template>
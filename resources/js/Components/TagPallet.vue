<script setup lang='ts'>
import Tag from '@/Components/Tag.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSeedling, faShare, faSpellCheck, faCheck, faCheckDouble, faSortSizeUp } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faSeedling, faShare, faSpellCheck, faCheck, faCheckDouble, faSortSizeUp)
    
defineProps<{
    stateIcon: {
        tooltip?: string
        icon: string | string[]
        color?: string
    }
}>()


// const getClass = (colorName: string | undefined) => {
//     if (!colorName) return

//     if (colorName.includes('#')) {
//         return `bg-[${colorName}] border border-[${colorName}] text-[${colorName}]`
//     }
    
//     return `bg-${colorName}-100 border border-${colorName}-200 text-${colorName}-500`
// }

</script>

<template>
    <Tag :label="stateIcon.tooltip" :style="{
        background: stateIcon.color ? stateIcon.color + '19' : '',
        border: `1px solid ${stateIcon.color ? stateIcon.color + 'AA' : ''}`,
        color: stateIcon.color ? `color-mix(in srgb, ${stateIcon.color} 90%, black)` : ''
    }">
        <template #label>
            <div>
                <FontAwesomeIcon :icon='stateIcon.icon' class='' fixed-width aria-hidden='true' />
                <span class="whitespace-nowrap capitalize">{{ stateIcon?.tooltip }}</span>
            </div>
        </template>
    </Tag>
</template>
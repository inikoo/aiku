<script setup lang='ts'>
import Tag from '@/Components/Tag.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSeedling, faShare, faSpellCheck, faCheck, faCheckDouble } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faSeedling, faShare, faSpellCheck, faCheck, faCheckDouble)
    
const props = defineProps<{
    label: string
    icon: string | string[]
}>()


// Select Tag's theme depends on enum delivery
const getTagTheme = (tagLabel: string) => {
    switch (tagLabel) {
        case 'In process': return 6
        case 'Submitted': return 4
        case 'Confirmed': return 5
        case 'Received': return 2
        case 'Done': return 3
        default: return 99
    }
}
</script>

<template>
    <Tag :label="label" :theme="getTagTheme(label)">
        <template #label>
            <FontAwesomeIcon :icon='icon' class='' fixed-width aria-hidden='true' />
            {{ label }}
        </template>
    </Tag>
</template>
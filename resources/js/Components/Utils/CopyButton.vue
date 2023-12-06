<script setup lang='ts'>
import { useCopyText } from '@/Composables/useCopyText'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCopy } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faCopy)

const props = defineProps<{
    text: string | number
}>()
</script>

<template>
    <div @click="useCopyText(text)" class="py-0.5 px-1 inline-flex leading-none text-gray-300 hover:text-gray-400 active:text-gray-600 cursor-pointer">
        <FontAwesomeIcon icon='fal fa-copy' class='' aria-hidden='true' />
    </div>
</template>
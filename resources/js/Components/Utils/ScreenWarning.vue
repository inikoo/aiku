<script setup lang='ts'>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faExclamationTriangle } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faExclamationTriangle)
</script>

<template>
    <div class="z-30 fixed top-0 left-0 w-full bg-red-500 text-white flex items-center justify-center gap-x-2 py-1">
        <FontAwesomeIcon icon='fas fa-exclamation-triangle' class='' fixed-width aria-hidden='true' />
        <span class="text-xs">This is staging and may send real data, pay attention about what you do.</span>
        <FontAwesomeIcon icon='fas fa-exclamation-triangle' class='' fixed-width aria-hidden='true' />  
    </div>
</template>
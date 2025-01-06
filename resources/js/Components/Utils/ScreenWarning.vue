<script setup lang='ts'>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faExclamationTriangle } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faExclamationTriangle)
</script>

<template>
    <div class="z-30 fixed top-0 left-0 w-full bg-red-500 text-white flex items-center justify-center gap-x-2 py-1">
        <FontAwesomeIcon icon='fas fa-exclamation-triangle' class='text-xs' fixed-width aria-hidden='true' />
        <slot>
            <span class="text-sm">
                Warning: You are currently in the staging environment.  Data can be delayed and overwritten at any time.
            </span>
        </slot>
        <FontAwesomeIcon icon='fas fa-exclamation-triangle' class='text-xs' fixed-width aria-hidden='true' />  
    </div>
</template>
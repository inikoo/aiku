<script setup lang='ts'>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSpinnerThird } from '@fad'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faSpinnerThird)
</script>

<template>
    <FontAwesomeIcon icon='fad fa-spinner-third' class='animate-spin' fixed-width aria-hidden='true' />
</template>
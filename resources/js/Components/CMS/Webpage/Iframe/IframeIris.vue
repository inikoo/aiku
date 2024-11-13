<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faPresentation, faLink, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { getStyles } from "@/Composables/styles.js"

library.add(faPresentation, faLink, faPaperclip)

const props = defineProps<{
    modelValue: any
    emptyState?: Boolean
    isEditable?: boolean
    properties?: any
}>()


</script>

<template>

    <div class="relative">
        <iframe :src="modelValue?.link" :style="getStyles(modelValue?.container?.properties)" title="I farme Block">
        </iframe>
    </div>
</template>
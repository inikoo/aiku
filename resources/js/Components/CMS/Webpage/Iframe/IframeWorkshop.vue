<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faPresentation, faLink, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { getStyles } from "@/Composables/styles.js"
/* import PureInput from "@/Components/Pure/PureInput.vue"
import { ref } from "vue"
import Popover from '@/Components/Popover.vue'
import Button from '@/Components/Elements/Buttons/Button.vue';
import InputUseOption from "@/Components/Pure/InputUseOption.vue" */


library.add(faPresentation, faLink, faPaperclip)

const props = defineProps<{
    modelValue: any
	webpageData: any
	blockData: Object
}>()

/* const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
    (e: 'autoSave'): void
}>() */


/* const onEnter = (e) => {
    emits('update:modelValue', { ...props.modelValue, emptyState: false })
    emits('autoSave')
} */
</script>

<template>
    <div type="button" v-if="!modelValue.link || modelValue?.link == ''"
        class="relative block w-full p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        <font-awesome-icon :icon="['fal', 'paperclip']" class="mx-auto h-12 w-12 text-gray-400" />
        <span class="mt-2 block text-sm font-semibold text-gray-900">I Frame</span>
    </div>

    <div v-else class="relative">
        <iframe :src="modelValue?.link" :style="getStyles(modelValue?.container?.properties)"  />
    </div>
</template>
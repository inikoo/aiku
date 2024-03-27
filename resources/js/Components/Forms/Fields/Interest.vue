<script setup lang="ts">
import PureInput from "@/Components/Pure/PureInput.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faExclamationCircle, faCheckCircle } from '@fas'
import { faCopy } from '@fal'
import { faSpinnerThird } from '@fad'
import { library } from "@fortawesome/fontawesome-svg-core"
import TabSelector from '@/Components/Elements/TabSelector.vue'
import { set, get } from "lodash"
library.add(faExclamationCircle, faCheckCircle, faSpinnerThird, faCopy)
import { ref, watch, defineEmits } from "vue"

const props = defineProps<{
    form: any
    fieldName: string
    options: {
        value: string
        label: string
    }[]
}>()

</script>

<template>
    <div class="relative">
        <TabSelector :optionRadio="options" :radioValue="form[fieldName]" />
    </div>
    <p v-if="get(form, ['errors', `${fieldName}`])" class="mt-2 text-sm text-red-600" :id="`${fieldName}-error`">
        {{ form.errors[fieldName] }}
    </p>
</template>
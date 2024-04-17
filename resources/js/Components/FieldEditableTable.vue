<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from "vue"
import PureInput from "@/Components/Pure/PureInput.vue"
import { useForm } from "@inertiajs/vue3"
import { faCheckCircle, faTimesCircle } from "@fas"
import { faTrashAlt } from "@far"
import { faSignOutAlt } from "@fal"
import { get, isNull } from 'lodash'
import { faSpinnerThird } from '@fad'
import { cloneDeep } from "lodash"
import PureMultiselect from "./Pure/PureMultiselect.vue"

library.add(faTrashAlt, faSignOutAlt, faTimesCircle, faSpinnerThird, faCheckCircle)

const props = withDefaults(defineProps<{
    data: { [key: string]: string }
    fieldName: string
    placeholder?: string
    type?: string
    min?: number
    max?: number
    fieldType?: String
    options:Array
}>(), {
    type: 'text',
    min: 0,
    max: 0,
    options:[],
    fieldType: 'input'
})

const emits = defineEmits<{
    (e: 'onSave', data: {}, fieldName: string): void
    (e: 'input', event: {}): void
}>()


const pallet = ref(
    cloneDeep(
        {
            ...props.data,
            form: useForm({ ...props.data, [`${props.fieldName}`]: isNull(props.data[props.fieldName]) ? "" : props.data[props.fieldName] })
        }
    )
)

// console.log(pallet, props.fieldName)

// On blur and press enter in Input
const onSaveInput = (value: string) => {
    if (value != props.data[props.fieldName]) {
        emits('onSave', pallet.value, props.fieldName)
    }
}

const onInput = (event) => {
    pallet.value.form.errors[props.fieldName] = ''
    emits('input', event)
}

</script>

<template>
    <div v-if="fieldType == 'input'">
        <PureInput v-model="pallet.form[fieldName]" @blur="(value) => onSaveInput(value)"
            @onEnter="(value) => onSaveInput(value)" @input="onInput" :suffix="true" :placeholder="placeholder"
            :type="type" :minValue="min" :maxValue="max">
            <template #suffix>
                <div class="flex justify-center items-center px-2 absolute inset-y-0 right-0 gap-x-1 cursor-pointer">
                    <span v-if="get(pallet, ['form', 'processing'], false)">
                        <FontAwesomeIcon :icon="['fad', 'spinner-third']" class='animate-spin' fixed-width
                            aria-hidden="true" />
                    </span>
                    <span
                        v-if="!get(pallet, ['form', 'hasErrors'], false) && !get(pallet, ['form', 'processing'], false) && get(pallet, ['form', 'wasSuccessful'], false)">
                        <FontAwesomeIcon :icon="['fas', 'check-circle']" fixed-width class="text-green-500"
                            aria-hidden="true" />
                    </span>
                    <span
                        v-if="get(pallet, ['form', 'hasErrors'], false) && !get(pallet, ['form', 'processing'], false) && !get(pallet, ['form', 'wasSuccessful'], false)">
                        <FontAwesomeIcon :icon="['fas', 'times-circle']" fixed-width class="text-red-500"
                            aria-hidden="true" />
                    </span>
                </div>
            </template>
        </PureInput>
    </div>
    <div v-else-if="fieldType == 'select'">
        <PureMultiselect
          :modelValue="pallet.form[fieldName]"
          :placeholder="placeholder"
          :options="options"
          @OnChange="(value) => onSaveInput(value)"
        >
        </PureMultiselect>
    </div>


    <div v-if="get(pallet, ['form', 'errors', `${fieldName}`])" class="mt-1 mb-1 w-fit italic text-sm text-red-500">
        {{ get(pallet, ['form', 'errors', `${fieldName}`]) }}
    </div>
    <!-- <pre>{{ pallet.form.errors[fieldName] }}</pre> -->
</template>
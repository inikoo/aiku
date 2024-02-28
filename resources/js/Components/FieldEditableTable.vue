<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from "@/Components/Table/Table.vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";
import { ref } from "vue";
import PureInput from "@/Components/Pure/PureInput.vue";
import axios from "axios";
import { notify } from "@kyvg/vue3-notification";
import { Link, useForm } from "@inertiajs/vue3";
import Icon from "@/Components/Icon.vue";
import { faTimesSquare, faCheckCircle, faTimesCircle } from "@fas";
import { faTrashAlt, faPaperPlane, faInventory } from "@far";
import { faSignOutAlt, faTruckLoading } from "@fal";
import { get, isNull } from 'lodash'
import { faSpinnerThird } from '@fad'
import { cloneDeep } from "lodash";

library.add(
    faTrashAlt, faSignOutAlt, faPaperPlane, faInventory, faTruckLoading, faTimesCircle, faSpinnerThird, faCheckCircle
);

const props = defineProps<{
    data: object,
    fieldName?: string
}>();

const emits = defineEmits<{
    (e: 'onSave', data: object, fieldName : string): void
}>()


const pallet = ref(
    cloneDeep(
        { ...props.data, 
            form: useForm({ ...props.data, [`${props.fieldName}`] : isNull(props.data[props.fieldName]) ? "" : props.data[props.fieldName]  }) 
        }
    )
);

console.log(pallet,props.fieldName)


</script>
  
<template>
    <PureInput v-model="pallet.form[fieldName]" @blur="(value) => { if (value != data[fieldName] ) emits('onSave', pallet, fieldName) }"
        @onEnter="(value) => { if (value && value != data[fieldName]) emits('onSave', pallet, fieldName) }" :suffix="true">
        <template #suffix>
            <div class="flex justify-center items-center px-2 absolute inset-y-0 right-0 gap-x-1 cursor-pointer">
                <span v-if="get(pallet, ['form', 'processing'], false)">
                    <FontAwesomeIcon :icon="['fad', 'spinner-third']" class='animate-spin' fixed-width aria-hidden="true" />
                </span>
                <span
                    v-if="!get(pallet, ['form', 'hasErrors'], false) && !get(pallet, ['form', 'processing'], false) && get(pallet, ['form', 'wasSuccessful'], false)">
                    <FontAwesomeIcon :icon="['fas', 'check-circle']" fixed-width class="text-green-500"
                        aria-hidden="true" />
                </span>
                <span
                    v-if="get(pallet, ['form', 'hasErrors'], false) && !get(pallet, ['form', 'processing'], false) && !get(pallet, ['form', 'wasSuccessful'], false)">
                    <FontAwesomeIcon :icon="['fas', 'times-circle']" fixed-width class="text-red-500" aria-hidden="true" />
                </span>
            </div>
        </template>
    </PureInput>
    <p v-if="get(pallet, ['form', 'errors', `${fieldName}`])" class="mt-2 text-sm text-red-600">
        {{ get(pallet, ['form', 'errors', `${fieldName}`]) }}
    </p>
</template>
  
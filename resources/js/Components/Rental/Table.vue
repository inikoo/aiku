<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 23:44:10 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import PureInput from "@/Components/Pure/PureInput.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faExclamationCircle, faCheckCircle, faTrash, faEdit } from '@fas'
import { faCopy } from '@fal'
import { faTrash as farTrash } from '@far'
import { faSpinnerThird } from '@fad'
import { library } from "@fortawesome/fontawesome-svg-core"
import { set, get } from "lodash"
import { ref, watch, onMounted, onBeforeMount, isReadonly, inject } from "vue"
import SelectQuery from "@/Components/SelectQuery.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { notify } from "@kyvg/vue3-notification"
import axios from "axios"
import { v4 as uuidv4 } from 'uuid'
import Popover from '@/Components/Popover.vue'
import { trans } from "laravel-vue-i18n"
import Currency from "@/Components/Pure/Currency.vue"
import { layoutStructure } from '@/Composables/useLayoutStructure'


library.add(faExclamationCircle, faCheckCircle, faSpinnerThird, faCopy, faTrash, farTrash, faEdit)

const props = defineProps<{
    form: any
    bluprint: Any
    fieldName: string
    options?: any
    fieldData?: {
        type: string
        placeholder: string
        readonly?: boolean
        copyButton: boolean
        maxLength?: number
    }
}>()

console.log(props)


</script>


<template>
    <div class="-mx-4 -my-2  sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-visible shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th v-for="e in props.bluprint.column" cope="col"
                                class="px-3 py-3.5 text-left text-sm font-semibold min-w-40 max-w-80">
                                {{ e.title }}
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="(itemData, index) in fieldData[bluprint.key]" :key="itemData.email">
                            <td v-for="e in props.bluprint.column" :key="e.key" class="whitespace-nowrap px-3 py-4 text-sm ">
                                {{ itemData[e.key] }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
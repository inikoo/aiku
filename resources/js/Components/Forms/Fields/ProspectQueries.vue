<script setup lang="ts">

import { faEnvelope, faAsterisk, faCodeBranch, faTags } from '@fal/'
import { library } from '@fortawesome/fontawesome-svg-core'
import QueryInformatics from '@/Components/Queries/QueryInformatics.vue'
import { trans } from "laravel-vue-i18n"
import { onMounted } from 'vue'
import { isNull } from 'lodash'

library.add(faEnvelope, faAsterisk, faCodeBranch, faTags)
const props = defineProps<{
    form: {
        [key: string]: {
            recipient_builder_type: string
            recipient_builder_data: {
                query: object
            }
        }
    }
    fieldName: string
    tabName: string
    fieldData: any
    options: {
        data: {
            id: number
            name: string
            number_items: number
        }[]
    }
}>()

const emits = defineEmits<{
    (e: 'onUpdate'): void
}>()

onMounted(() => {
    if (!props.form[props.fieldName].recipient_builder_data.query || isNull(props.form[props.fieldName].recipient_builder_data.query)) {
        let index = -1;
        for (let i = 0; i < props.options.data.length; i++) {
            if (props.options.data[i].number_items > 0) {
                index = i;
                break;
            }
        }
        if(index != -1)
        props.form[props.fieldName].recipient_builder_data.query = { id: props.options.data[index].id }
    }

})

</script>

<template>
    <div>
        <table class="min-w-full divide-y divide-gray-300 border-b border-gray-200 text-xs">
            <thead>
                <tr class="text-left text-sm font-semibold text-gray-600">
                    <th scope="col" class="whitespace-nowrap pb-2.5 pl-4 pr-3 sm:pl-0">{{ trans('Name') }}</th>
                    <th scope="col" class="whitespace-nowrap px-2 pb-2.5">{{ trans('Description') }}</th>
                    <th scope="col" class="whitespace-nowrap px-2 pb-2.5">{{ trans('Prospects') }}</th>
                    <th scope="col" class="relative whitespace-nowrap pb-2.5 pl-3 pr-4 sm:pr-0">
                        <span class="sr-only">{{ trans('Edit') }}</span>
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="option in options.data" :key="option.id" class="" :class="[
                    option.id == form[fieldName].recipient_builder_data.query?.id ? 'bg-org-50 text-gray-600' : '',
                    option.number_items < 1 ? 'bg-gray-100 text-gray-400' : 'text-gray-500'  // If the prospects is 0
                ]">
                    <td class="py-2 pl-2 pr-4 ">{{ option.name }}</td>
                    <td>
                        <QueryInformatics :option="option" />

                    </td>
                    <td class="px-2 py-2 text-center tabular-nums">{{ option.number_items }}</td>
                    <td class="relative py-2 px-3 text-right font-medium">
                        <div v-if="option.number_items > 0">
                            <label :for="'radioProspects' + option.id"
                                class="bg-transparent absolute inset-0 cursor-pointer" />
                            <input v-model="form[fieldName].recipient_builder_data.query" :value="{ id: option.id }"
                                type="radio" :id="'radioProspects' + option.id" name="radioProspects"
                                class="appearance-none ring-1 ring-gray-400 text-org-600 focus:border-0 focus:outline-none focus:ring-0" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</template>

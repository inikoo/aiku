<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import { useLocaleStore } from "@/Stores/locale"
import Button from '@/Components/Elements/Buttons/Button.vue'
import { computed, ref, watch } from "vue"
import PureInputNumber from "@/Components/Pure/PureInputNumber.vue"
import { cloneDeep } from 'lodash'
import Popover from '@/Components/Popover.vue'
import SelectQuery from '@/Components/SelectQuery.vue'
import { useForm } from "@inertiajs/vue3"

const props = defineProps<{
    route_pallets: routeType
    data: Array<{ label: string, location: string, value: number }>
}>()

console.log('sss',props)

const cloneData = ref(cloneDeep(props.data))
const editable = ref(false)
const unlocatedPallet = ref(0)
const originalValues = ref(props.data.map(item => item.value))
const palletForm = useForm({
    pallet : null
})

const calculateUnlocatedPallet = () => {
    unlocatedPallet.value = originalValues.value.reduce((total, original, index) => {
        return total + (original - cloneData.value[index].value)
    }, 0)
}

watch(cloneData, calculateUnlocatedPallet, { deep: true })

const onCancel = () => {
    editable.value = false
    cloneData.value = cloneDeep(props.data)
}

</script>

<template>
    <div class="flex justify-between border-b border-gray-300 p-2">
        <div class="font-semibold">Stored Item in Pallet :</div>
        <div class="flex flex-shrink-0 gap-3">
            <Button v-if="!editable" type="edit" size="xs" @click="editable = true" />
            <Button v-if="editable" type="tertiary" label="Cancel" size="xs" @click="onCancel" />
            <Button v-if="editable" :type="unlocatedPallet != 0 ? 'disabled' : 'save'" size="xs" label="Save"
                :icon="['fas', 'fa-save']" @click="editable = false" />
        </div>
    </div>
    <div v-if="editable" class="flex justify-between align-middle">
        <div class="text-sm text-red-500 font-medium px-2 py-4 ">
            Unlocated pallet: {{ unlocatedPallet }}
        </div>
        <div class="px-2 py-4">
            <Popover position="right-10">
                <template #button>
                    <Button type="dashed" :icon='["fas", "fa-plus"]' label="Row" size="xs" />
                </template>

                <template #content="{ close: closed }">
                    <div class="w-64">
                        <SelectQuery 
                            :urlRoute="route(route_pallets.name,route_pallets.parameters)" 
                            :value="palletForm" 
                            :placeholder="'Select Pallet'" 
                            :required="true" 
                            :trackBy="'reference'"
                            :label="'reference'" 
                            :valueProp="'id'" 
                            :closeOnSelect="true" 
                            :clearOnSearch="false"
                            :fieldName="'pallet'" 
                            @updateVModel="() => error.pallet = ''" 
                        />
                    </div>
                    <div class="py-3">
                    <Button full type="save" />
                    </div>
                </template>
            </Popover>

        </div>
    </div>

    <div class="mt-2 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <table class="min-w-full divide-y divide-gray-500 border border-gray-500 rounded-lg overflow-hidden">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-sm font-semibold text-start border-b border-gray-500">
                                Pallet name
                            </th>
                            <th scope="col" class="px-4 py-3 text-sm font-semibold text-start border-b border-gray-500">
                                Location
                            </th>
                            <th scope="col" class="px-4 py-3 text-sm font-semibold text-start border-b border-gray-500">
                                Qty
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <tr v-for="(pallet, index) in cloneData" :key="pallet.label + index" class="even:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3 text-sm border-b border-gray-500">
                                {{ pallet.label }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm border-b border-gray-500">
                                {{ pallet.location }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm border-b border-gray-00 w-32">
                                <span v-if="!editable">{{ useLocaleStore().number(pallet.value) }}</span>
                                <PureInputNumber v-else v-model="pallet.value" :maxValue="data[index].value"
                                    :minValue="0" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

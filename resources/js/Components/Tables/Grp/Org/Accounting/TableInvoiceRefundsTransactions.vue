<script setup lang='ts'>

import Table from '@/Components/Table/Table.vue'
import { inject } from 'vue'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { routeType } from '@/types/route'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { trans } from 'laravel-vue-i18n'
import PureInput from '@/Components/Pure/PureInput.vue'
import InputNumber from 'primevue/inputnumber'
import { get, set } from 'lodash'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSave } from '@fad'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faSave)

defineProps<{
    data: object
    tab: string
}>()

const locale = inject('locale', aikuLocaleStructure)

// Section: Refund
const isLoading = ref<number[]>([])
const onClickRefund = (routeRefund: routeType, slugRefund: number) => {
    router[routeRefund.method || 'post'](
        route(routeRefund.name, routeRefund.parameters),
        {

        },
        {
            onStart: () => {
                isLoading.value?.push(slugRefund)
            },
            onFinish: () => {
                const index = isLoading.value.indexOf(slugRefund)
                if (index > -1) {
                    isLoading.value.splice(index, 1)
                }
            }
        }
    )
}

const isLoadingQuantity = ref<number[]>([])
const onClickQuantity = (routeRefund: routeType, slugRefund: number, amount: number) => {
    router[routeRefund.method || 'post'](
        route(routeRefund.name, routeRefund.parameters),
        {
            refund_amount: amount
        },
        {
            onStart: () => {
                isLoadingQuantity.value?.push(slugRefund)
            },
            onFinish: () => {
                const index = isLoadingQuantity.value.indexOf(slugRefund)
                if (index > -1) {
                    isLoadingQuantity.value.splice(index, 1)
                }
            }
        }
    )
}

</script>

<template>
    <div class="h-min">
        <Table :resource="data" :name="tab">
            <template #cell(net_amount)="{ item }">
                <div :class="item.net_amount < 0 ? 'text-red-500' : ''">
                    {{ locale.currencyFormat(item.currency_code, item.net_amount) }}
                </div>
            </template>

            <template #cell(action)="{ item, proxyItem }">
                <!-- <pre>{{ item }}</pre> -->
                <Button
                    v-if="!item.in_process"
                    @click="onClickRefund(item.refund_route, item.code)"
                    :label="trans('Refund')"
                    icon="fal fa-plus"
                    type="secondary"
                    :loading="isLoading.includes(item.code)"
                />

                <div class="flex items-center">
                    <InputNumber
                        :modelValue="get(proxyItem, ['refund_amount'], 0)"
                        @update:modelValue="(value) => (console.log(value), set(proxyItem, ['refund_amount'], value))"
                        inputClass="width-12"
                    >
                    
                    </InputNumber>
                    
                    <FontAwesomeIcon v-if="true" @click="() => onClickQuantity(item.refund_route, item.code, get(proxyItem, ['refund_amount'], 0))" icon="fad fa-save" class="h-8 cursor-pointer" :style="{ '--fa-secondary-color': 'rgb(0, 255, 4)' }" aria-hidden="true" />
                    <FontAwesomeIcon v-else icon="fal fa-save" class="h-8 text-gray-300" aria-hidden="true" />
                </div>

            </template>
        </Table>
    </div>
</template>

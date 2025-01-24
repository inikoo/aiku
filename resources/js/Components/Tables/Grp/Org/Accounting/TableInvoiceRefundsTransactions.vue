<script setup lang='ts'>

import Table from '@/Components/Table/Table.vue'
import { inject } from 'vue'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { routeType } from '@/types/route'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { trans } from 'laravel-vue-i18n'

defineProps<{
    data: object
    tab: string
}>()

const locale = inject('locale', aikuLocaleStructure)

// Section: Refund
const isLoading = ref<number[]>([])
const onClickRefund = (routeRefund: routeType, idRefund: number) => {
    router[routeRefund.method || 'post'](
        route(routeRefund.name, routeRefund.parameters),
        {

        },
        {
            onStart: () => {
                isLoading.value?.push(idRefund)
            },
            onFinish: () => {
                const index = isLoading.value.indexOf(idRefund)
                if (index > -1) {
                    isLoading.value.splice(index, 1)
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

            <template #cell(action)="{ item }">
                <Button
                    v-if="!item.in_process"
                    @click="onClickRefund(item.refund_route, item.id)"
                    :label="trans('Refund')"
                    icon="fal fa-plus"
                    type="secondary"
                    :loading="isLoading.includes(item.id)"
                />
            </template>
        </Table>
    </div>
</template>

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 23 May 2024 09:45:43 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import Icon from "@/Components/Icon.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faRobot, faBadgePercent, faTag } from '@fal'
import { useLocaleStore } from '@/Stores/locale'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { inject, ref } from "vue"
import PureInput from '@/Components/Pure/PureInput.vue'
import { layoutStructure } from '@/Composables/useLayoutStructure'
import { routeType } from '@/types/route'
import Tag from '@/Components/Tag.vue'

library.add(faRobot)

const props = defineProps<{
    data: {}
    state: string
    tab?: string
}>()

const layout = inject('layout', layoutStructure)
const emits = defineEmits<{
    (e: 'renderTableKey'): void
}>()

// const layout = inject('layout', layoutStructure)

// function serviceRoute(service: {}) {
//     // console.log(route().current())
//     switch (route().current()) {

//         case "grp.org.fulfilments.show.billables.services.index":
//             return route(
//                 'grp.org.fulfilments.show.billables.services.show',
//                 [route().params['organisation'], route().params['fulfilment'], service.slug])
//         default:
//             return null
//     }
// }

// Section: Quantity
const isLoading = ref<string | boolean>(false)
const onUpdateQuantity = (idFulfilmentTransaction: number, value: number) => {
    const routeDelete = <routeType>{}
    if (layout.app.name === 'Aiku') {
        routeDelete.name = 'grp.models.fulfilment-transaction.update'
        routeDelete.parameters = {fulfilmentTransaction: idFulfilmentTransaction}
    } else {
        routeDelete.name = 'retina.models.fulfilment-transaction.update'
        routeDelete.parameters = {fulfilmentTransaction: idFulfilmentTransaction}
    }
    router.patch(
        route(routeDelete.name, routeDelete.parameters),
        {
            quantity: value
        },
        {
            onStart: () => isLoading.value = 'quantity' + idFulfilmentTransaction,
            onFinish: () => isLoading.value = false
        }
    )
}
const onDeleteTransaction = (idFulfilmentTransaction: number) => {
    const routeDelete = <routeType>{}
    if (layout.app.name === 'Aiku') {
        routeDelete.name = 'grp.models.fulfilment-transaction.delete'
        routeDelete.parameters = {fulfilmentTransaction: idFulfilmentTransaction}
    } else {
        routeDelete.name = 'retina.models.fulfilment-transaction.delete'
        routeDelete.parameters = {fulfilmentTransaction: idFulfilmentTransaction}
    }
    router.delete(
        route(routeDelete.name, routeDelete.parameters),
        {
            onStart: () => isLoading.value = 'buttonReset' + idFulfilmentTransaction,
            onFinish: () => isLoading.value = false
        }
    )
}

</script>

<template>
    <!-- <pre>{{ data.data[0] }}</pre> -->
    <Table :key="tab" :resource="data" :name="tab" class="mt-5">
        <!-- Column: Code -->
        <template #cell(code)="{ item }">
            {{ item.asset_code || '-' }}
        </template>

        <!-- Column: Name -->
        <template #cell(name)="{ item }">
            {{ item['asset_name'] }} ({{ useLocaleStore().currencyFormat(item['currency_code'],
                item['asset_price'])}}/{{ item['unit_abbreviation'] }})
            <Tag v-if="item['discount'] > 0" :theme="17">
                <template #label>
                    <font-awesome-icon :icon="faTag" class="text-xs text-emerald-700"/>
                    {{ item['discount'] }}%
                </template>
            </Tag>
        </template>

        <!-- Column: Quantity -->
        <template #cell(quantity)="{ item }">
            <div
            v-if="state === 'in-process'" class="w-32">
                <PureInput
                    :modelValue="item.quantity"
                    @onEnter="(e: number) => item.is_auto_assign ? false : onUpdateQuantity(item.id, e)"
                    @blur="(e: string) => item.is_auto_assign ? false : e == item.quantity ? false : onUpdateQuantity(item.id, e)"
                    :isLoading="isLoading === 'quantity' + item.id"
                    type="number"
                    align="right"
                    :readonly="item.is_auto_assign"
                    v-tooltip="item.is_auto_assign ? `Auto assign, can't change quantity.` : undefined"
                />
            </div>

            <div v-else>{{ item.quantity }}</div>
        </template>

        <!-- Column: Net -->
        <template #cell(net_amount)="{ item }">
            {{ useLocaleStore().currencyFormat(item.currency_code, item.total) }}
        </template>

        <!-- Column: Action -->
        <template #cell(actions)="{ item }">
            <Button
                v-if="!item.is_auto_assign && state === 'in-process'"
                @click="() => onDeleteTransaction(item.id)"
                :loading="isLoading === 'buttonReset' + item.id"
                icon="fal fa-times"
                type="negative"
                v-tooltip="'Unselect this field'"
            />
        </template>
    </Table>
</template>

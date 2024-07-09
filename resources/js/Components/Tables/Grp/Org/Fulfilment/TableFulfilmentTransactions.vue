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
import { faRobot } from '@fal'
import { useLocaleStore } from '@/Stores/locale'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { inject, ref } from "vue"
import PureInput from '@/Components/Pure/PureInput.vue'
import { layoutStructure } from '@/Composables/useLayoutStructure'

library.add(faRobot)

const props = defineProps<{
    data: {}
    state: string
    tab?: string
}>()

const isActionLoading = ref<string | boolean>(false)
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
    router.patch(
        route('grp.models.fulfilment-transaction.update', {fulfilmentTransaction: idFulfilmentTransaction}),
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
    router.delete(
        route('grp.models.fulfilment-transaction.delete', {fulfilmentTransaction: idFulfilmentTransaction}),
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
        </template>

        <!-- Column: Quantity -->
        <template #cell(quantity)="{ item }">
            <PureInput
                v-if="state === 'in-process'"
                v-model="item.quantity"
                @blur="(e: number) => item.is_auto_assign ? false : onUpdateQuantity(item.id, e)"
                :isLoading="isLoading === 'quantity' + item.id"
                type="number"
                :readonly="item.is_auto_assign"
                v-tooltip="item.is_auto_assign ? `Auto assign, can't change quantity.` : undefined"
            />

            <div v-else>{{ item.quantity }}</div>
        </template>

        <!-- Column: Net -->
        <template #cell(net)="{ item }">
            {{ useLocaleStore().currencyFormat(item.currency_code, item.total) }}
        </template>

        <!-- Column: Action -->
        <template #cell(actions)="{ item }">
            <Button
                v-if="!item.is_auto_assign"
                @click="() => onDeleteTransaction(item.id)"
                :loading="isLoading === 'buttonReset' + item.id"
                icon="fal fa-times"
                type="negative"
                v-tooltip="'Unselect this field'"
            />
        </template>
    </Table>
</template>

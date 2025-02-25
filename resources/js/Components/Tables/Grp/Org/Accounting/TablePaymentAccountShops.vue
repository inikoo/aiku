<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 16:45:18 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { PaymentAccount } from "@/types/payment-account"
import { faBox, faHandHoldingBox, faPallet, faPencil } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Shop } from '@/types/shop'
import { useLocaleStore } from '@/Stores/locale'
library.add(faBox, faHandHoldingBox, faPallet, faPencil)

defineProps<{
    data: {}
    tab?: string
}>()

const locale = useLocaleStore();

</script>


<template>
    <!-- {{ props.shopsList }} -->
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(number_payments)="{ item: paymentAccountShops }">
            {{ useLocaleStore().number(paymentAccountShops.number_payments) }}
        </template>
        <template #cell(amount_successfully_paid)="{ item: paymentAccountShop }">
            <div class="text-gray-500">{{ locale.currencyFormat( paymentAccountShop.shop_currency_code, paymentAccountShop.amount_successfully_paid)  }}</div>
        </template>
    </Table>
</template>

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 16:45:18 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { PaymentAccount } from "@/types/payment-account"
import Multiselect from '@vueform/multiselect'
import { onMounted, onUnmounted, ref } from 'vue'
import axios from 'axios'
import { notify } from '@kyvg/vue3-notification'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faBox, faHandHoldingBox, faPallet, faPencil } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Shop } from '@/types/shop'
library.add(faBox, faHandHoldingBox, faPallet, faPencil)

const props = defineProps<{
    data: {}
    tab?: string
    shopsList?: Shop[]
}>()

function paymentAccountRoute(paymentAccount: PaymentAccount) {
    switch (route().current()) {

        case 'grp.org.accounting.org-payment-service-providers.show':
        case 'grp.org.accounting.org-payment-service-providers.show.payment-accounts.index':
            return route(
                'grp.org.accounting.org-payment-service-providers.show.payment-accounts.show',
                [route().params['organisation'], route().params['orgPaymentServiceProvider'], paymentAccount.slug])
        case 'grp.org.accounting.payment-accounts.index':
            return route(
                'grp.org.accounting.payment-accounts.show',
                [route().params['organisation'], paymentAccount.slug])

    }

}

function providersRoute(paymentAccount: PaymentAccount) {
    // console.log(route().current())
    switch (route().current()) {


        case 'grp.org.accounting.payment-accounts.index':
            return route(
                'grp.org.accounting.org-payment-service-providers.show',
                [route().params['organisation'], paymentAccount.slug])

    }
}

function paymentsRoute(paymentAccount: PaymentAccount) {
    switch (route().current()) {

        case 'grp.org.accounting.org-payment-service-providers.show.payment-accounts.index':
            return route(
                'grp.org.accounting.org-payment-service-providers.show.payment-accounts.show.payments.index',
                [route().params['organisation'], route().params['orgPaymentServiceProvider'], paymentAccount.payment_service_provider_slug])
        case 'grp.org.accounting.payment-accounts.index':
            return route(
                'grp.org.accounting.payment-accounts.show.payments.index',
                [route().params['organisation'], paymentAccount.slug])

    }
}


// On update data Tags (add tag or delete tag)
const isEditShop = ref(false)
const setUpdateShop = async (paymentId: string, shopId: number) => {
    try {
        const response = await axios.patch(route('grp.models.shop.payment-accounts.sync', {
            paymentAccount: paymentId,
            shop: shopId,
        }))

        console.log('zzz', response.data)
        // Refetch the data of Table to update the item.tags (v-model doesn't work)
        router.reload({
            only: ['data']
        })
    } catch (error: any) {
        console.error(error)
        notify({
            title: "Failed to update shop",
            text: error,
            type: "error"
        })
    }
}


onMounted(async () => {
    // Listening to 'esc' keyboard
    if (typeof window !== 'undefined') {
        document.addEventListener('keydown', (e) => e.keyCode == 27 ? isEditShop.value = false : '')
    }

})

onUnmounted(() => {
    document.removeEventListener('keydown', () => false)
})

</script>


<template>
    <!-- {{ props.shopsList }} -->
    <Table :resource="data" :name="tab" class="mt-5">
        <!-- Column: Code-->
        <template #cell(code)="{ item: paymentAccount }">
            <Link :href="paymentAccountRoute(paymentAccount)" class="specialUnderline">
                {{ paymentAccount['code'] }}
            </Link>
        </template>

        <!-- Column: Provider -->
        <template #cell(payment_service_provider_code)="{ item: paymentAccount }">
            <Link :href="providersRoute(paymentAccount)" class="specialUnderlineSecondary">
                {{ paymentAccount['payment_service_provider_code'] }}
            </Link>
        </template>

        <!-- Column: Payment -->
        <template #cell(number_payments)="{ item: paymentAccount }">
            <Link :href="paymentsRoute(paymentAccount)" class="specialUnderlineSecondary">
                {{ paymentAccount['number_payments'] }}
            </Link>
        </template>

        <!-- Column: Shop -->
        <template #cell(shop_name)="{ item: paymentAccount }">
            <div class="min-w-[200px] relative p-0">
            <!-- <pre>{{ paymentAccount }}</pre> -->
                <div v-if="isEditShop !== paymentAccount.slug" class="flex gap-x-1 gap-y-1.5 mb-2">
                    <div v-if="paymentAccount.shop_name">
                        {{ paymentAccount.shop_name }}
                    </div>

                    <div v-else class="italic text-gray-400">
                        No shop selected
                    </div>

                    <!-- Icon: pencil -->
                    <div class="transition flex items-center px-1" @click="() => isEditShop = paymentAccount.slug">
                        <FontAwesomeIcon icon='fal fa-pencil' class='text-gray-400 text-sm cursor-pointer hover:text-gray-500' fixed-width aria-hidden='true' />
                    </div>
                </div>

                <div v-else>
                    <Multiselect v-model="paymentAccount.shop_code"
                        :key="paymentAccount.slug"
                        mode="single"
                        placeholder="Select shop"
                        valueProp="id"
                        trackBy="id"
                        label="name"
                        @change="(shopId: number) => (console.log('jjj', shopId), setUpdateShop(paymentAccount.id, shopId))"
                        :close-on-select="true"
                        :searchable="true"
                        :caret="true"
                        :options="props.shopsList"
                    />

                    <div class="text-gray-400 italic text-xs">
                        Press Esc to finish edit or <span @click="() => isEditShop = false" class="hover:text-gray-500 cursor-pointer">click here</span>.
                    </div>
                </div>
            </div>
        </template>
    </Table>
</template>

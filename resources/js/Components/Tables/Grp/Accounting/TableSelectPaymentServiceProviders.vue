<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 10 Apr 2024 16:09:45 Central Indonesia Time, Sanur , Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { SelectPaymentServiceProvider } from "@/types/select-payment-service-provider"
import { faPlus, faCheckDouble } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Modal from "@/Components/Utils/Modal.vue"
import { ref } from 'vue'
import { trans } from 'laravel-vue-i18n'
import AccountProvidersForm from '@/Components/PaymentProviders/accountProvidersForm.vue'
library.add(faPlus, faCheckDouble)

const props = defineProps<{
    data: {},
    tab?: string,
    paymentAccountTypes: {}
    organisation_id: string
}>()

const openModal = ref(false)
const selectedProvider = ref(null)
const form = useForm({
    code: null,
    name: null
})

function paymentServiceProviderRoute(paymentServiceAccount: SelectPaymentServiceProvider) {
    switch (route().current()) {
        case 'grp.org.accounting.org-payment-service-providers.index':
            return route(
                'grp.org.accounting.org-payment-service-providers.show',
                [route().params['organisation'], paymentServiceAccount.org_slug])

        default:
            return null

    }

}
function paymentAccountRoute(paymentServiceAccount: SelectPaymentServiceProvider) {
    switch (route().current()) {
        case 'grp.org.accounting.org-payment-service-providers.index':
            return route(
                'grp.org.accounting.org-payment-service-providers.show.payment-accounts.index',
                [
                    route().params['organisation'],
                    paymentServiceAccount.org_slug
                ]
            )

    }

}

function paymentsRoute(paymentServiceAccount: SelectPaymentServiceProvider) {
    switch (route().current()) {

        case 'grp.org.accounting.org-payment-service-providers.show.payment-accounts.index':
            return route(
                'grp.org.accounting.org-payment-service-providers.show.payment-accounts.show.payments.index',
                [
                    route().params['organisation'],
                    route().params['paymentServiceProvider'],
                    route().params['paymentAccount']]
            )
        case 'grp.org.accounting.payment-accounts.index':
            return route(
                'grp.org.accounting.payment-accounts.show.payments.index',
                [
                    route().params['organisation'],
                    route().params['paymentAccount']
                ]
            )
        case 'grp.org.accounting.org-payment-service-providers.index':
            return route(
                'grp.org.accounting.org-payment-service-providers.show.payments.index',
                [
                    route().params['organisation'],
                    paymentServiceAccount.org_slug
                ]
            )

    }

}


const onOpenModal = (data) => {
    selectedProvider.value = data
    openModal.value = true
}

const onCloseModal = async (data) => {
    openModal.value = false
    setTimeout(() => {
        selectedProvider.value = null
    }, 300)
}

</script>


<template>
    <Table :resource="data" class="mt-5">
        <!-- Column: State -->
        <template #cell(adoption)="{ item: item }">
            <!--   <pre>{{ item }}</pre> -->
            <div class="flex justify-center">
                <template v-if="item.state == 'active'">
                    <div v-if="item.number_payment_accounts && item.number_payment_accounts > 0" v-tooltip="trans('Account')">
                        <FontAwesomeIcon icon='fal fa-check-double' class='' fixed-width aria-hidden='true' />
                    </div>
                    <div v-else v-tooltip="'Create Account'">
                        <FontAwesomeIcon icon='fas fa-plus' @click="() => onOpenModal(item)" class="px-1 cursor-pointer text-gray-400 hover:text-gray-600" fixed-width aria-hidden='true' />
                    </div>
                </template>

                <template v-else-if="item.state === 'legacy'"></template>
            </div>
        </template>

        <!-- Column: Code -->
        <template #cell(code)="{ item: paymentServiceProvider }">
            <Link v-if="paymentServiceProvider['org_slug']" :href="paymentServiceProviderRoute(paymentServiceProvider)"
                class="specialUnderline">
                {{ paymentServiceProvider['org_code'] }}
            </Link>
            
            <span v-else class="px-1">
                {{ paymentServiceProvider['code'] }}
            </span>
        </template>

        <!-- Column: Account -->
        <template #cell(number_payment_accounts)="{ item: paymentServiceProvider }">
            <Link v-if="paymentServiceProvider['org_slug']" :href="paymentAccountRoute(paymentServiceProvider)"
                class="specialUnderlineSecondary">
                {{ paymentServiceProvider['number_payment_accounts'] }}
            </Link>
        </template>
        
        <!-- Column: Payment -->
        <template #cell(number_payments)="{ item: paymentServiceProvider }">
            <Link v-if="paymentServiceProvider['org_slug']" :href="paymentsRoute(paymentServiceProvider)"
                class="specialUnderlineSecondary">
                {{ paymentServiceProvider['number_payments'] }}
            </Link>
        </template>
    </Table>

    <!-- Section: Modal -->
    <Modal :isOpen="openModal" @onClose="onCloseModal" width="w-2/5" class="overflow-visible">
        <div>
            <AccountProvidersForm :organisation_id="organisation_id" :provider="selectedProvider"
                :onCloseModal="onCloseModal" :paymentAccountTypes="paymentAccountTypes" />
        </div>
    </Modal>
</template>

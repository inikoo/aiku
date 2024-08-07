<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Jun 2023 20:46:53 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head,  } from '@inertiajs/vue3'
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { router } from '@inertiajs/vue3'
import type { Component } from 'vue'
import TableRentalAgreementClauses from "@/Components/Tables/Grp/Org/Fulfilment/TableRentalAgreementClauses.vue"
import { useTabChange } from "@/Composables/tab-change"
import { computed, defineAsyncComponent, inject, onMounted, onUnmounted, ref } from "vue"
import Tabs from "@/Components/Navigation/Tabs.vue"

import FulfilmentCustomerShowcase from "@/Components/Showcases/Grp/FulfilmentCustomerShowcase.vue"

import { Action } from '@/types/Action'

import { trans } from 'laravel-vue-i18n'
import {
    faStickyNote,
    faPallet,
    faUser,
    faNarwhal,
    faTruckCouch,
    faFileInvoiceDollar,
    faSignOutAlt,
    faPaperclip,
    faPaperPlane,
    faCheckDouble,
    faShare,
    faTruckLoading,
    faFileInvoice,
    faExclamationTriangle, faUsdCircle
} from '@fal'
import { notify } from '@kyvg/vue3-notification'
import { PageHeading as PageHeadingTypes } from "@/types/PageHeading";
import type { Navigation } from "@/types/Tabs";
import Modal from "@/Components/Utils/Modal.vue"
import Button from '@/Components/Elements/Buttons/Button.vue'
import { layoutStructure } from '@/Composables/useLayoutStructure'
library.add(faStickyNote, faUser, faNarwhal, faTruckCouch, faPallet, faFileInvoiceDollar, faSignOutAlt, faPaperclip, faPaperPlane, faCheckDouble, faShare, faTruckLoading, faFileInvoice, faExclamationTriangle, faUsdCircle)

const ModelChangelog = defineAsyncComponent(() => import('@/Components/ModelChangelog.vue'))

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes,
    tabs: {
        current: string
        navigation: Navigation;
    }
    showcase?: {}
    agreed_prices?: {}
}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {

    const components: Component = {
        showcase: FulfilmentCustomerShowcase,
        agreed_prices: TableRentalAgreementClauses,
    }

    return components[currentTab.value]

})



const isOpen = ref(false)
const warehouseValue = ref(null)
const errorMessage = ref(null)
const isModalOpen = ref(false)
const layout = inject('layout', layoutStructure)
const loadingCreatePalletDelivery = ref(false)



const onButtonCreateDeliveryClick = (action: Action) => {
    console.log('action', action)
    if (action.disabled) isModalOpen.value = true
    
    else {
        router[action.route?.method || 'get'](
            route(action.route?.name || 'grp', action.route?.parameters),
            {},
            {
                onBefore: () => { loadingCreatePalletDelivery.value = true },
                onSuccess: () => {
                    console.log('on sucess aciton')
                },
                onError: (error: {}) => {
                    loadingCreatePalletDelivery.value = false,
                    notify({
                        title: 'Something went wrong.',
                        text: Object.values(error || {}).join(', '),
                        type: 'error',
                    })
                }
            }
        )
    }
}



onMounted(() => {
    window.Echo.private(`grp.${layout.group?.id}.fulfilmentCustomer.${layout.user.id}`).listen('.PalletDelivery', (e) => {
        notify({
            title: e.data.title,
            text: e.data.text,
            type: "info"
        })
    })
})

onUnmounted(() => {
    window.Echo.private(`grp.${layout.group?.id}.fulfilmentCustomer.${layout.user.id}`).stopListening('.PalletDelivery')
})


</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>

    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab]" :tab="currentTab"></component>

    <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false" width="w-1/4">
        <div class="text-center">
            <font-awesome-icon :icon="['fal', 'exclamation-triangle']"  class="mx-auto h-12 w-12 text-gray-400"/>
            <h3 class="mt-2 text-sm font-semibold text-gray-900">{{trans('You Dont Have Rental Agreement')}}</h3>
            <p class="mt-1 text-sm text-gray-500">{{trans('You need to make a rental agreement first to continue this')}}.</p>
            <div class="mt-6">
            </div>
        </div>
    </Modal>

</template>


<style src="@vueform/multiselect/themes/default.css"></style>

<style>
/* Style for multiselect globally */
.multiselect-option.is-selected,
.multiselect-option.is-selected.is-pointed {
    background: var(--ms-option-bg-selected, #6366f1) !important;
    color: var(--ms-option-color-selected, #fff) !important;
}

.multiselect-option.is-selected.is-disabled {
    background: var(--ms-option-bg-selected-disabled, #c7d2fe);
    color: var(--ms-option-color-selected-disabled, #818cf8);
}

.multiselect.is-active {
    border: var(--ms-border-width-active, var(--ms-border-width, 1px)) solid var(--ms-border-color-active, var(--ms-border-color, #d1d5db));
    box-shadow: 0 0 0 var(--ms-ring-width, 3px) var(--ms-ring-color, rgba(99, 102, 241, 0.188));
}
</style>

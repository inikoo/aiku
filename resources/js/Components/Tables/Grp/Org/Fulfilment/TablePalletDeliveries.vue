<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 May 2024 15:09:29 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus } from "@fas"
import { faCheckDouble, faShare, faCross } from "@fal"
import Modal from "@/Components/Utils/Modal.vue"
import { ref } from 'vue'
// import EmptyState from '@/Components/Utils/EmptyState.vue'


import { PalletDelivery } from "@/types/pallet-delivery"
import Icon from "@/Components/Icon.vue"
import { useFormatTime, useDaysLeftFromToday } from '@/Composables/useFormatTime'
import { routeType } from "@/types/route"
import { notify } from "@kyvg/vue3-notification"
import { trans } from "laravel-vue-i18n"

library.add(faPlus, faCheckDouble, faShare, faCross)

const props = defineProps<{
    data: {}
    tab?: string
}>()

const openModal = ref(false)
const loading = ref(false)

function palletDeliveryRoute(palletDelivery: PalletDelivery) {

    switch (route().current()) {
        case 'grp.org.warehouses.show.incoming.pallet_deliveries.index':
            return route(
                'grp.org.warehouses.show.incoming.pallet_deliveries.show',
                [
                    route().params['organisation'],
                    route().params['warehouse'],
                    palletDelivery.slug
                ])
        case 'grp.org.fulfilments.show.operations.pallet-deliveries.index':
            return route(
                'grp.org.fulfilments.show.operations.pallet-deliveries.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    palletDelivery.slug
                ])
        case 'grp.org.warehouses.show.dispatching.pallet-returns.index':
            return route(
                'grp.org.warehouses.show.dispatching.pallet-returns.show',
                [
                    route().params['organisation'],
                    route().params['warehouse'],
                    palletDelivery.slug
                ])
        case 'grp.org.fulfilments.show.crm.customers.show.pallet_returns.index':
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallet_returns.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['fulfilmentCustomer'],
                    palletDelivery.slug
                ])
        case 'grp.overview.fulfilment.pallet-deliveries.index':
            return route(
                'grp.org.fulfilments.show.operations.pallet-deliveries.show',
                [
                    palletDelivery.organisation_slug,
                    palletDelivery.fulfilment_slug,
                    palletDelivery.slug
                ])
        case 'retina.fulfilment.storage.pallet_deliveries.index':
            return route(
                'retina.fulfilment.storage.pallet_deliveries.show',
                [
                    palletDelivery.slug
                ])
        default:
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['fulfilmentCustomer'],
                    palletDelivery.slug
                ])
    }
}

function customerRoute(palletDelivery: PalletDelivery) {
    // console.log('pp', route().params, palletDelivery)
    switch (route().current()) {
        case 'grp.org.fulfilments.show.operations.pallet-deliveries.index':
            if (palletDelivery?.customer_slug) {
                return route(
                    'grp.org.fulfilments.show.crm.customers.show',
                    [
                        route().params['organisation'],
                        route().params['fulfilment'],
                        palletDelivery.customer_slug
                    ])
            }
        default:
            return null
    }
}




const onClickNewPalletDelivery = (action: Action) => {
    if (action.disabled) openModal.value = true
    else {
        const href = action.route?.name ? route(action.route?.name, action.route?.parameters) : action.route?.name ? route(action.route?.name, action.route?.parameters) : '#'
        const method = action.route?.method ?? 'get'
        router[method](
            href,
            {
                onBefore: () => { loading.value = true },
                onerror: () => { loading.value = false }
            })
    }
};


const isLoading = ref<string | boolean>(false)
const onClickReceived = (receivedRoute: routeType) => {
    router[receivedRoute.method || 'post'](
        route(receivedRoute.name, receivedRoute.parameters),
        {},
        {
            onStart: () => isLoading.value = 'received',
            onError: () => {
                notify({
                    title: trans('Something went wrong'),
                    text: trans('Failed to update the Delivery status'),
                    type: 'error',
                })
            },
            onFinish: () => isLoading.value = false,
        }
    )
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">

        <template #button-new-pallet-delivery="{ linkButton }">
            <Button :style="linkButton.style" :icon="linkButton.icon" :label="linkButton.label" size="l"
                :loading="loading" @click="() => onClickNewPalletDelivery(linkButton)" />
        </template>

        <!-- Column: Reference -->
        <template #cell(reference)="{ item: palletDelivery }">
            <Link :href="palletDeliveryRoute(palletDelivery)" class="primaryLink">
                {{ palletDelivery['reference'] }}
            </Link>
        </template>

        <!-- Column: Customer -->
        <template #cell(customer_name)="{ item: palletDelivery }">
            <Link v-if="customerRoute(palletDelivery)" :href="customerRoute(palletDelivery)" class="secondaryLink">
                {{ palletDelivery['customer_name'] }}
            </Link>
            <div v-else>
                {{ palletDelivery['customer_name'] || '-' }}
            </div>
        </template>

        <!-- Column: Customer Reference -->
        <template #cell(customer_reference)="{ item: palletDelivery }">
            <Link v-if="customerRoute(palletDelivery)" :href="customerRoute(palletDelivery)" class="secondaryLink">
                {{ palletDelivery.customer_reference }}
            </Link>
            
            <div v-else class="text-gray-600">
                {{ palletDelivery.customer_reference || '-'}}
            </div>
        </template>

        <!-- Column: State -->
        <template #cell(state)="{ item: palletDelivery }">
            <Icon :data="palletDelivery['state_icon']" class="px-1" />
        </template>

        <!-- Column: Estiamted Delivery Date -->
        <template #cell(estimated_delivery_date)="{ item: palletDelivery }">
            <div>
                {{ useFormatTime (palletDelivery.estimated_delivery_date) }}
                <span v-if="palletDelivery.state === 'in_process' || palletDelivery.state === 'submitted' || palletDelivery.state === 'confirmed'" class="text-gray-400">
                    ({{useDaysLeftFromToday(palletDelivery.estimated_delivery_date)}})
                </span>
            </div>
        </template>

        <template #cell(actions)="{ item }">
            <Button
                v-if="item.state === 'confirmed'"
                @click="() => onClickReceived(item.receiveRoute)"
                label="receive"
                :loading="isLoading == 'received'"
            />
        </template>
        <!-- <template #buttondeliveries="{ linkButton: linkButton }">
            <Link v-if="linkButton?.route?.name" method="post"
                as="div"
                :href="route(linkButton?.route?.name, linkButton?.route?.parameters)"
                class="ring-1 ring-gray-300 overflow-hidden first:rounded-l last:rounded-r">
                <Button
                    :style="linkButton.style"
                    :label="linkButton.label"
                    class="rounded-none text-sm border-none font-medium shadow-sm focus:ring-transparent focus:ring-offset-transparent focus:ring-0"
                />
            </Link>
        </template> -->
    </Table>

    <Modal :isOpen="openModal" @onClose="openModal = false" width="w-2/4">
        <main class="grid min-h-full place-items-center bg-white px-6 py-24 sm:py-32 lg:px-8">
            <div class="text-center">
                <p class="text-base font-semibold text-indigo-600">403 - Access Denied</p>
                <h1 class="mt-4 text-xl font-bold tracking-tight text-gray-900 sm:text-xl">You do not have permission
                    to
                    access this page</h1>
                <p class="mt-6 text-sm leading-7 text-gray-600">Sorry, the page you are looking for could not be
                    found.
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6">

                    <div @click="() => openModal = false" class="text-sm font-semibold text-gray-900">Close </div>

                    <div
                        class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Contact Suport<span aria-hidden="true">&rarr;</span>
                    </div>
                </div>
            </div>
        </main>
    </Modal>
</template>

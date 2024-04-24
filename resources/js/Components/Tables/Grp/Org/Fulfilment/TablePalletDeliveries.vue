<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 25 Feb 2024 10:30:47 Central Standard Time, Mexico City, Mexico
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
import EmptyState from '@/Components/Utils/EmptyState.vue'


import { PalletDelivery } from "@/types/pallet-delivery"
import Icon from "@/Components/Icon.vue"

library.add(faPlus, faCheckDouble, faShare, faCross)

const props = defineProps<{
    data: {}
    tab?: string
}>()

const openModal = ref(false)
const loading = ref(false)

function palletDeliveryRoute(palletDelivery: PalletDelivery) {
    switch (route().current()) {
        case 'grp.org.warehouses.show.fulfilment.pallet-deliveries.index':
            return route(
                'grp.org.warehouses.show.fulfilment.pallet-deliveries.show',
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
        case 'grp.org.warehouses.show.fulfilment.pallet-returns.index':
            return route(
                'grp.org.warehouses.show.fulfilment.pallet-returns.show',
                [
                    route().params['organisation'],
                    route().params['warehouse'],
                    palletDelivery.slug
                ])
        case 'grp.org.fulfilments.show.crm.customers.show.pallet-returns.index':
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallet-returns.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['fulfilmentCustomer'],
                    palletDelivery.slug
                ])
        default:
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['fulfilmentCustomer'],
                    palletDelivery.slug
                ])
    }
}

function customerRoute(palletDelivery: PalletDelivery) {
    switch (route().current()) {
        case 'grp.org.fulfilments.show.operations.pallet-deliveries.index':
            return route(
                'grp.org.fulfilments.show.crm.customers.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    palletDelivery.customer_slug
                ])
    }
}




const handleClick = (action: Action) => {
    if (action.disabled) openModal.value = true
    else {
        const href = action.route?.name ? route(action.route?.name, action.route?.parameters) : action.href?.name ? route(action.href?.name, action.href?.parameters) : '#'
        const method = action.route?.method ?? 'get'
        router[method](
            href,
            {
                onBefore: () => { loading.value = true },
                onerror: () => { loading.value = false }
            })
    }
};

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">

        <template #emptyState="data">
            <EmptyState>
                <template #button-empty-state>
                    <div>
                        <!--    <Link v-if="data?.action" as="div"
                            :href="route(data?.action.route.name, data?.action.route.parameters)"
                            :method="data?.action?.route?.method" class="mt-4 block"> -->
                        <Button :style="data.emptyState.action?.style"
                            @click="() => handleClick(data.emptyState.action)" :icon="data.emptyState.action?.icon"
                            :label="data.emptyState.action?.tooltip" />
                        <!--    </Link> -->
                    </div>
                </template>
            </EmptyState>
        </template>

        <template #button-new-pallet-delivery="{ linkButton }">
            <Button :style="linkButton.style" :icon="linkButton.icon" :label="linkButton.label" size="l"
                :loading="loading" @click="() => handleClick(linkButton)" />
        </template>

        <!-- Column: Reference -->
        <template #cell(reference)="{ item: palletDelivery }">
            <Link :href="palletDeliveryRoute(palletDelivery)" class="specialUnderline">
            {{ palletDelivery['reference'] }}
            </Link>
        </template>

        <!-- Column: Customer -->
        <template #cell(customer_name)="{ item: palletDelivery }">
            <Link :href="customerRoute(palletDelivery)" class="specialUnderlineSecondary">
            {{ palletDelivery['customer_name'] }}
            </Link>
        </template>

        <!-- Column: State -->
        <template #cell(state)="{ item: palletDelivery }">
            <Icon :data="palletDelivery['state_icon']" class="px-1" />
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

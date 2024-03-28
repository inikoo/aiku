<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 25 Feb 2024 10:30:47 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus } from "@fas"
import { faCheckDouble, faShare, faCross } from "@fal"

import { PalletDelivery } from "@/types/pallet-delivery"
import Icon from "@/Components/Icon.vue"

library.add(faPlus, faCheckDouble, faShare, faCross)

const props = defineProps<{
    data: {}
    tab?: string
}>()

function palletDeliveryRoute(palletDelivery: PalletDelivery) {
    // console.log(route().current())
    switch (route().current()) {
        case 'grp.org.warehouses.show.fulfilment.pallet-deliveries.index':
            return route(
                'grp.org.warehouses.show.fulfilment.pallet-deliveries.show',
                [
                    route().params['organisation'],
                    route().params['warehouse'],
                    palletDelivery.reference
                ])
        case 'grp.org.fulfilments.show.operations.pallet-deliveries.index':
            return route(
                'grp.org.warehouses.show.fulfilment.pallet-deliveries.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    palletDelivery.reference
                ])
        case 'grp.org.warehouses.show.fulfilment.pallet-returns.index':
            return route(
                'grp.org.warehouses.show.fulfilment.pallet-returns.show',
                [
                    route().params['organisation'],
                    route().params['warehouse'],
                    palletDelivery.reference
                ])
        case 'grp.org.fulfilments.show.crm.customers.show.pallet-returns.index':
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallet-returns.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['fulfilmentCustomer'],
                    palletDelivery.reference
                ])
        default:
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['fulfilmentCustomer'],
                    palletDelivery.reference
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



</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <!-- Column: Reference -->
        <template #cell(reference)="{ item: palletDelivery }">
            <Link :href="palletDeliveryRoute(palletDelivery)" class="specialUnderlineSecondary">
                {{ palletDelivery['reference'] }}
            </Link>
        </template>

        <!-- Column: Customer -->
        <!-- <template #cell(customer_name)="{ item: palletDelivery }">
            <Link :href="customerRoute(palletDelivery)" class="specialUnderline">
                {{ palletDelivery['customer_name'] }}
            </Link>
        </template> -->

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
</template>

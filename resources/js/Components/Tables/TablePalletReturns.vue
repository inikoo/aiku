<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 25 Jan 2024 11:46:16 Malaysia Time, Bali Office, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus } from "@fas"

import { PalletDelivery } from "@/types/pallet-delivery";
import Icon from "@/Components/Icon.vue";

library.add(faPlus)

const props = defineProps<{
    data: object
    tab?: string
}>()

function palletReturnRoute(palletReturn: PalletDelivery) {
    switch (route().current()) {
        case 'grp.org.warehouses.show.fulfilment.pallet-returns.index':
            return route(
                'grp.org.warehouses.show.fulfilment.pallet-returns.show',
                [
                    route().params['organisation'],
                    route().params['warehouse'],
                    palletReturn.slug
                ]);
        case 'grp.org.fulfilments.show.operations.pallet-returns.index':
            return route(
                'grp.org.fulfilments.show.operations.pallet-returns.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    palletReturn.slug
                ]);
        default:
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallet-returns.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['fulfilmentCustomer'],
                    palletReturn.slug
                ]);
    }
}



</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">


        <template #cell(reference)="{ item: palletReturn }">
            <Link :href="palletReturnRoute(palletReturn)" class="specialUnderline">
                {{ palletReturn['reference'] }}
            </Link>
        </template>

        <template #cell(state)="{ item: palletReturn }">
            <Icon :data="palletReturn['state_icon']" class="px-1"/>
        </template>


        <template #buttonreturns="{ linkButton: linkButton }">
            <Link
                v-if="linkButton?.route?.name"
                method="post"
                :href="route(linkButton?.route?.name, linkButton?.route?.parameters)"
                class="ring-1 ring-gray-300 overflow-hidden first:rounded-l last:rounded-r">
                <Button
                    :style="linkButton.style"
                    :label="linkButton.label"
                    class="h-full capitalize inline-flex items-center rounded-none text-sm border-none font-medium shadow-sm focus:ring-transparent focus:ring-offset-transparent focus:ring-0">
                </Button>
            </Link>
        </template>
    </Table>
</template>

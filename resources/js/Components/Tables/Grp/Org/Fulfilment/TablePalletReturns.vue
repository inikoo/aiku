<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 May 2024 18:46:51 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus } from "@fas"
import TagPallet from '@/Components/TagPallet.vue'

import { PalletDelivery } from "@/types/pallet-delivery"
import Icon from "@/Components/Icon.vue"
import { inject } from "vue"
import { layoutStructure } from "@/Composables/useLayoutStructure"

library.add(faPlus)

const props = defineProps<{
    data: {}
    tab?: string
}>()

const layout = inject('layout', layoutStructure)

function palletReturnRoute(palletReturn: PalletDelivery) {
    switch (route().current()) {
        case 'grp.org.warehouses.show.dispatching.pallet-returns.index':
            return route(
                'grp.org.warehouses.show.dispatching.pallet-returns.show',
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
        case 'retina.storage.pallet-returns.index':
            return route(
                'retina.storage.pallet-returns.show',
                [
                    palletReturn.slug
                ]);
        default:
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallet_returns.show',
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
        <!-- Column: Reference -->
        <template #cell(reference)="{ item: palletReturn }">
            <Link :href="palletReturnRoute(palletReturn)" class="primaryLink">
                {{ palletReturn['reference'] }}
            </Link>
        </template>

        <!-- Column: State -->
        <template #cell(state)="{ item: palletReturn }">
            <TagPallet v-if="layout.app.name == 'retina'" :stateIcon="palletReturn.state_icon" />
            <Icon v-else :data="palletReturn['state_icon']" class="px-1"/>
        </template>

        <template #cell(type)="{ item: palletReturn }">
            <Icon :data="palletReturn['type_icon']" class="px-1"/>
        </template>

        <!-- Column: Pallets -->
        <template #cell(pallets)="{ item: palletReturn }">
            <div class="tabular-nums">
                {{ palletReturn.number_pallets }}
            </div>

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

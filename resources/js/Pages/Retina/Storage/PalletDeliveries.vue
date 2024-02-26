<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 23 Feb 2024 09:56:34 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup  lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus } from "@fas"
import { Link } from "@inertiajs/vue3"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Table from "@/Components/Table/Table.vue"

import { PalletDelivery } from "@/types/pallet-delivery"
import TagPallete from '@/Components/TagPallete.vue'

library.add(faPlus)

const props = defineProps<{
    data: object
    title: string
    pageHead: object
}>()

function palletDeliveryRoute(palletDelivery: PalletDelivery) {
    switch (route().current()) {
        default:
            return route(
                'retina.storage.pallet-deliveries.show',
                [
                    palletDelivery.slug
                ])

    }
}

</script>

<template layout="Retina">
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>

    <Table :resource="data" class="mt-5">
        <template #cell(reference)="{ item: palletDelivery }">
            <Link :href="palletDeliveryRoute(palletDelivery)" class="specialUnderline">
                {{ palletDelivery['reference'] }}
            </Link>
        </template>

        <template #cell(state)="{ item: palletDelivery }">
            <TagPallete :label="palletDelivery.state_icon.tooltip" :icon="palletDelivery.state_icon.icon" />
        </template>


        <template #buttondeliveries="{ linkButton: linkButton }">
            <Link v-if="linkButton?.route?.name" method="post"
                :href="route(linkButton?.route?.name, linkButton?.route?.parameters)"
                class="ring-1 ring-gray-300 overflow-hidden first:rounded-l last:rounded-r">
                <Button :style="linkButton.style" :label="linkButton.label"
                    class="h-full capitalize inline-flex items-center rounded-none text-sm border-none font-medium shadow-sm focus:ring-transparent focus:ring-offset-transparent focus:ring-0">
                </Button>
            </Link>
        </template>
    </Table>
</template>

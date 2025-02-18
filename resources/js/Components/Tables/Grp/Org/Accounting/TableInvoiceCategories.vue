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
library.add(faBox, faHandHoldingBox, faPallet, faPencil)

defineProps<{
    data: {}
    tab?: string
}>()

function invoiceCategoryRoute(invoiceCategory: {}) {
    switch (route().current()) {
        case "grp.org.accounting.invoice-categories.index":
            return route(
                "grp.org.accounting.invoice-categories.show",
                [route().params["organisation"], invoiceCategory.slug])
        default:
            return ''
    }
}

</script>


<template>
    <!-- {{ props.shopsList }} -->
    <Table :resource="data" :name="tab" class="mt-5">
      <template #cell(name)="{ item: invoiceCategory }">
            <Link :href="invoiceCategoryRoute(invoiceCategory)" class="primaryLink">
                {{ invoiceCategory["name"] }}
            </Link>
        </template>
    </Table>
</template>

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
import { useLocaleStore } from '@/Stores/locale'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
import { inject } from 'vue'
import Icon from '@/Components/Icon.vue'
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

function invoiceRoute(invoiceCategory: {}) {
    switch (route().current()) {
        case "grp.org.accounting.invoice-categories.index":
            return route(
                "grp.org.accounting.invoice-categories.show.invoices.index",
                [route().params["organisation"],invoiceCategory.slug])
        default:
            return ''
    }
}

function refundRoute(invoiceCategory: {}) {
    switch (route().current()) {
        case "grp.org.accounting.invoice-categories.index":
        return route(
                "grp.org.accounting.invoice-categories.show.invoices.index",
                {
                    "organisation": route().params["organisation"],
                    "invoiceCategory": invoiceCategory.slug,
                    'tab': 'refunds'
                })
        default:
            return ''
    }
}

const locale = inject('locale', aikuLocaleStructure)


</script>


<template>
    <!-- {{ props.shopsList }} -->
    <Table :resource="data" :name="tab" class="mt-5">
      <template #cell(state_icon)="{ item: invoiceCategory }">
            <Icon :data="invoiceCategory.state_icon" />
        </template>
      <template #cell(name)="{ item: invoiceCategory }">
            <Link :href="invoiceCategoryRoute(invoiceCategory)" class="primaryLink">
                {{ invoiceCategory["name"] }}
            </Link>
        </template>
      <template #cell(number_type_invoices)="{ item: invoiceCategory }">
            <Link :href="invoiceRoute(invoiceCategory)" class="primaryLink">
                {{ invoiceCategory["number_type_invoices"] }}
            </Link>
        </template>
      <template #cell(number_type_refunds)="{ item: invoiceCategory }">
            <Link :href="refundRoute(invoiceCategory)" class="primaryLink">
                {{ invoiceCategory["number_type_refunds"] }}
            </Link>
        </template>
      <template #cell(amount)="{ item: invoiceCategory }">
            {{ locale.currencyFormat(invoiceCategory.currency_code, invoiceCategory["amount"]) }}
        </template>
    </Table>
</template>

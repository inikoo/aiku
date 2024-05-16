<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 19:24:57 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { Payment } from "@/types/payment";
import { useFormatTime } from "@/Composables/useFormatTime copy";

const props = defineProps<{
    data: object,
    tab?: string
}>();


function paymentsRoute(payment: Payment) {
    console.log(route().current());
    switch (route().current()) {

        case "grp.org.accounting.payments.index":
            return route(
                "grp.org.accounting.payments.show",
                [route().params["organisation"], payment.slug]);
    }

}
</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(reference)="{ item: payment }">
            <Link :href="paymentsRoute(payment)">
                {{ payment["reference"] }}
            </Link>
        </template>
      <template #cell(date)="{ item }">
        <div class="text-gray-500">
          {{ useFormatTime(item.date) }}
        </div>
      </template>
    </Table>
</template>



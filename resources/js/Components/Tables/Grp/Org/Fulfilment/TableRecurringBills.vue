<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 27 Apr 2024 18:34:20 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang='ts'>
import Table from '@/Components/Table/Table.vue'
import {RecurringBill} from "@/types/recurring_bill";
import {Link} from "@inertiajs/vue3";
import {Pallet} from "@/types/Pallet";


const props = defineProps<{
  data: object
  tab?: string,
}>()


function recurringBillRoute(bill) {
    console.log(route().current());
    switch (route().current()) {
        case "grp.org.fulfilments.show.crm.customers.show.recurring_bills.index":
            return route(
                "grp.org.fulfilments.show.crm.customers.show.recurring_bills.show",
                [
                    route().params["organisation"],
                    route().params["fulfilment"],
                    route().params["fulfilmentCustomer"],
                    bill.slug
                ]);

        default:
            return [];
    }
}
</script>

<template>
  <Table :resource="data" :name="tab" class="mt-5">
      <template #cell(reference)="{ item: bill }">
          <Link :href="recurringBillRoute(bill)" class="secondaryLink">
              {{ bill["reference"] }}
          </Link>
      </template>
  </Table>
</template>

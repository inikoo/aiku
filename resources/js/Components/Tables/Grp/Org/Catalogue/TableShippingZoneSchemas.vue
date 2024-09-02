<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
  import {Link} from "@inertiajs/vue3";
  import Table from "@/Components/Table/Table.vue";
  import type {Links, Meta} from "@/types/Table";
  
  defineProps<{
    data: {
      data: {}
      links: Links
      meta: Meta
    },
    tab?: string
  }>();
  
  
//   function shopRoute(schema: {}) {
//     console.log(route().current())
//     switch (route().current()) {
//       case "grp.org.shops.show.assets.shipping.index":
//         return route(
//             "grp.org.shops.show.crm.show.orders.show",
//             [route().params["organisation"],,route().params["shop"], route().params["customer"], order.slug]);
//       case "grp.org.shops.show.ordering.orders.index":
//         return route(
//             "grp.org.shops.show.ordering.orders.show",
//             [ route().params["organisation"],route().params["shop"],order.slug]);
//       case "grp.org.shops.show.crm.customers.show.orders.index":
//         return route(
//             "grp.org.shops.show.crm.customers.show.orders.show",
//             [ route().params["organisation"],route().params["shop"],route().params["customer"],order.slug]);
//       case "grp.org.shops.show.crm.customers.show.customer-clients.orders.index":
//         return route(
//             "grp.org.shops.show.crm.customers.show.customer-clients.orders.show",
//             [ route().params["organisation"],route().params["shop"],route().params["customer"],route().params["customerClient"],order.slug]);
//       default:
//         return null;
//     }
//   }
  
  
  </script>
  
  <template>
    <Table :resource="data" :name="tab" class="mt-5">
      <template #cell(name)="{ item: schema }">
          {{ schema["name"] }}
      </template>
    </Table>
  </template>
  
  
  
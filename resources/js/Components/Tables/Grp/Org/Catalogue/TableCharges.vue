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
  
  
  function shopRoute(charge: {}) {
    console.log(route().current())
    switch (route().current()) {
      case "grp.org.shops.show.assets.charges.index":
        return route(
            "grp.org.shops.show.assets.charges.show",
            [route().params["organisation"],route().params["shop"], charge.slug]);
      default:
        return null;
    }
  }
  
  
  </script>
  
  <template>
    <Table :resource="data" :name="tab" class="mt-5">
      <template #cell(code)="{ item: charge }">
        <Link :href="shopRoute(charge)" class="primaryLink">
        {{ charge["code"] }}
      </Link>
      </template>
    </Table>
  </template>
  
  
  
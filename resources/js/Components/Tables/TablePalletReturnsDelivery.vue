<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 25 Jan 2024 11:46:16 Malaysia Time, Bali Office, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
import Table from '@/Components/Table/Table.vue';
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faTrashAlt, faPaperPlane } from '@far';
import { faSignOutAlt } from '@fal';
import {Link} from '@inertiajs/vue3';

library.add(
    faTrashAlt, faSignOutAlt, faPaperPlane
)
const props = defineProps<{
    data: object,
    tab?: string
    state?: string
}>()


  function customerRoute(pallet: object) {
            return route(pallet.deleteRoute.name,pallet.deleteRoute.params);
}


  </script>

  <template>
      <Table :resource="data" :name="tab" class="mt-5">
          <template #cell(actions)="{ item: pallet }">
        <div v-if="props.state == 'in-process'">
            <Link :href="customerRoute(pallet)" method="delete">
                <font-awesome-icon class="text-red-600" :icon="['far', 'trash-alt']" />
            </Link>

        </div>
        <div v-else>
            <font-awesome-icon  :icon="['far', 'paper-plane']" />
        </div>

        </template>
      </Table>
  </template>

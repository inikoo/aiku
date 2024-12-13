<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';

defineProps<{
    data: object,
    tab?:string,
}>()


// function mailRoute(postRoom: PostRoom) {
//     switch (route().current()) {
//         case 'mail.post_rooms.index':
//             return route(
//                 'mail.post_rooms.show',
//                 [postRoom.code]);
//     }
// }

function postRoomRoute(orgPostRoom: {}) {
      switch (route().current()) {
        //   case 'grp.org.shops.show.comms.post-rooms.index':
        //   return route(
        //           'grp.org.shops.show.comms.post-rooms.show',
        //           [route().params['organisation'], route().params['shop'], orgPostRoom.slug])
          default:
              return null
      }
  }

</script>

<template>
    <Table :resource="data" :name="tab">
      <template #cell(name)="{ item: postRoom }">
          <Link :href="postRoomRoute(postRoom)" class="primaryLink">
              {{ postRoom["name"] }}
          </Link>
      </template>
    </Table>
</template>


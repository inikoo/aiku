<!--
  -  Author: Vika Aqordi <aqordivika@yahoo.co.id>
  -  Created: Mon, 15 July 2024 17:33:07 Bali, Indonesia
  -  Copyright (c) 2024, Vika Aqordi
  -->

  <script setup lang="ts">
  import { Head } from '@inertiajs/vue3'
  import PageHeading from '@/Components/Headings/PageHeading.vue'
  import { capitalize } from "@/Composables/capitalize"
  import { PageHeading as TSPageHeading } from '@/types/PageHeading'
  import Table from '@/Components/Table/Table.vue'
  
  const props = defineProps<{
      data: {}
      title: string
      pageHead: TSPageHeading
  }>()
  
  </script>
  
  <template>
      <Head :title="capitalize(title)" />
      <PageHeading :data="pageHead" />
  
      <Table :resource="data" class="mt-5">
      </Table>
  </template>
<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 May 2024 08:59:19 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { Agent } from "@/types/agent";
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue";

defineProps<{
  data: object,
  tab?: string
}>();

console.log(route().current())
function agentRoute(agent: Agent) {
  switch (route().current()) {
    case "grp.org.procurement.org_agents.index":
      return route(
        "grp.org.procurement.org_agents.show",
        [route().params["organisation"], agent.slug]);
  }
}

</script>

<template>
  <Table :resource="data" :name="tab" class="mt-5">
    <template #cell(code)="{ item: agent }">
      <Link :href="agentRoute(agent)" class="primaryLink">
        {{ agent["code"] }}
      </Link>
    </template>
    <template #cell(location)="{ item: agent }">
      <AddressLocation :data="agent['location']" />
    </template>
  </Table>
</template>



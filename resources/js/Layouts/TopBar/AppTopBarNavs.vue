<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 03 Jul 2023 16:55:06 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { useLayoutStore } from "@/Stores/layout";
import AppTopBarNavProcurement from "@/Layouts/TopBar/AppTopBarNavProcurement.vue";
import AppTopBarNavItems from "@/Layouts/TopBar/AppTopBarNavItems.vue";
import AppTopBarNavDropdown from "@/Layouts/TopBar/AppTopBarNavDropdown.vue";
import AppTopBarNavsDropdownDependantItems from "@/Layouts/TopBar/AppTopBarNavsDropdownDependantItems.vue";

const layout = useLayoutStore();

</script>

<template>
    <div class="flex">
        <AppTopBarNavProcurement v-if="layout.currentModule==='procurement'"  />
        <AppTopBarNavItems />
        <AppTopBarNavDropdown   />
        <AppTopBarNavsDropdownDependantItems />

    </div>
</template>

